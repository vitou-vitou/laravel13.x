---
title: "How to Build Custom SMS OTP Screens in Keycloakify"
published: true
description: "Customize and validate SMS OTP authentication flows in Keycloakify for Keycloak custom Service Provider Interfaces (SPI)."
tags: "keycloak, keycloakify, otp, 2fa, authentication"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/72-keycloakify-sms-otp-custom-spi.md"
---

Default Keycloak distributions support Time-based One-Time Passwords (TOTP) through authenticator apps, but telecom and banking applications frequently require SMS OTP verification. Custom Java Service Provider Interfaces (SPIs) handle SMS dispatch, yet their accompanying FreeMarker templates often remain unstyled and fragile.

Keycloakify allows you to intercept custom SPI template IDs and render modern, accessible OTP input interfaces. Let's see how.

## The Architecture: Custom SPI and Frontend Contracts

In a custom SMS SPI (such as `keycloak-sms-otp-spi-latest.jar`), Keycloak challenges the user by rendering a custom FreeMarker file, typically named `sms-otp.ftl` or `login-sms.ftl`.

The custom authenticator passes dynamic context attributes to the template:

- `phoneNumber`: Masked recipient phone number (e.g., `+855 12 *** *89`).
- `otpLength`: Number of expected digits (often 4 or 6).
- `ttl`: Seconds until the code expires.
- `url.loginAction`: Submission target for OTP verification.

To support this screen, Keycloakify needs to recognize this non-standard template name and provide mock data for local testing.

## Step 1: Extend KcContext for the Custom SPI Page

Declare the custom page ID in your Keycloakify configuration:

`src/login/KcContext.ts:`
```typescript
import { createGetKcContext } from 'keycloakify/login';

export type KcContextExtensionPerPage = {
    'sms-otp.ftl': {
        phoneNumber?: string;
        otpLength?: number;
        ttl?: number;
        resendUrl?: string;
    };
};

export const { getKcContext } = createGetKcContext<
    {},
    KcContextExtensionPerPage
>({
    mockData: [
        {
            pageId: 'sms-otp.ftl',
            phoneNumber: '+855 12 ••• •89',
            otpLength: 6,
            ttl: 60,
            resendUrl: '#resend-mock',
            url: {
                loginAction: '#submit-otp',
            },
        },
    ],
});

export type KcContext = NonNullable<ReturnType<typeof getKcContext>['kcContext']>;
```

This configuration tells TypeScript and Vite that `sms-otp.ftl` is a valid authentication step.

## Step 2: Build the Split-Digit OTP Component

A polished SMS verification interface often splits digits into individual boxes rather than rendering a standard text field:

`src/login/pages/SmsOtp.tsx:`
```tsx
import { useState, useRef, useEffect, type ClipboardEvent, type KeyboardEvent } from 'react';
import type { KcContext } from '../KcContext';

type Props = {
    kcContext: Extract<KcContext, { pageId: 'sms-otp.ftl' }>;
};

export default function SmsOtp({ kcContext }: Props) {
    const { url, phoneNumber, otpLength = 6, ttl = 60, messagesPerField } = kcContext;
    const [digits, setDigits] = useState<string[]>(Array(otpLength).fill(''));
    const [timeLeft, setTimeLeft] = useState<number>(ttl);
    const inputRefs = useRef<(HTMLInputElement | null)[]>([]);

    useEffect(() => {
        if (timeLeft <= 0) return;
        const timer = setInterval(() => setTimeLeft((t) => t - 1), 1000);
        return () => clearInterval(timer);
    }, [timeLeft]);

    const handleInput = (index: number, value: string) => {
        const char = value.slice(-1);
        if (!/^\d*$/.test(char)) return;

        const newDigits = [...digits];
        newDigits[index] = char;
        setDigits(newDigits);

        if (char && index < otpLength - 1) {
            inputRefs.current[index + 1]?.focus();
        }
    };

    const handleKeyDown = (index: number, e: KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Backspace' && !digits[index] && index > 0) {
            inputRefs.current[index - 1]?.focus();
        }
    };

    const handlePaste = (e: ClipboardEvent<HTMLInputElement>) => {
        e.preventDefault();
        const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, otpLength);
        if (!pasted) return;

        const newDigits = [...digits];
        pasted.split('').forEach((char, i) => {
            newDigits[i] = char;
        });
        setDigits(newDigits);
        inputRefs.current[Math.min(pasted.length, otpLength - 1)]?.focus();
    };

    return (
        <div className="max-w-md mx-auto my-12 bg-white p-8 rounded-xl shadow-sm border border-slate-200">
            <h2 className="text-xl font-bold text-slate-800 text-center mb-2">Verify Phone Number</h2>
            <p className="text-sm text-slate-500 text-center mb-6">
                Enter the verification code sent to <span className="font-semibold text-slate-700">{phoneNumber}</span>
            </p>

            <form action={url.loginAction} method="post" className="space-y-6">
                {/* Hidden field submitting full concatenated OTP string to Keycloak backend */}
                <input type="hidden" name="smsCode" value={digits.join('')} />

                <div className="flex justify-center gap-2" onPaste={handlePaste}>
                    {digits.map((digit, index) => (
                        <input
                            key={index}
                            ref={(el) => (inputRefs.current[index] = el)}
                            type="text"
                            inputMode="numeric"
                            maxLength={1}
                            value={digit}
                            onChange={(e) => handleInput(index, e.target.value)}
                            onKeyDown={(e) => handleKeyDown(index, e)}
                            className="w-12 h-14 text-center text-xl font-bold border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 focus:outline-none transition"
                        />
                    ))}
                </div>

                {messagesPerField.existsError('smsCode') && (
                    <p className="text-xs text-red-600 text-center font-medium">
                        {messagesPerField.getFirstError('smsCode')}
                    </p>
                )}

                <button
                    type="submit"
                    disabled={digits.some((d) => !d)}
                    className="w-full py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 text-white font-medium rounded-lg text-sm transition"
                >
                    Verify & Continue
                </button>

                <div className="text-center text-xs text-slate-500">
                    {timeLeft > 0 ? (
                        <span>Resend code in {timeLeft}s</span>
                    ) : (
                        <a href={kcContext.resendUrl || '#'} className="text-blue-600 hover:underline font-semibold">
                            Resend Verification Code
                        </a>
                    )}
                </div>
            </form>
        </div>
    );
}
```

The split input design provides a modern user experience while the hidden `smsCode` input ensures complete compatibility with the custom SPI's form parser.

## Step 3: Register the Custom SPI Route in KcApp

Add the custom page to your master template switch:

`src/login/KcApp.tsx:`
```tsx
case 'sms-otp.ftl':
    return <SmsOtp kcContext={kcContext} />;
```

When the custom SMS authenticator executes in Keycloak, Keycloakify intercepts `sms-otp.ftl` and mounts your customized interface.

## What Can Go Wrong

- **Mismatched Form Input Names**: Keycloak SPIs look for specific form parameters (e.g., `smsCode`, `code`, or `otp`). If your hidden input name diverges from the Java authenticator's `context.getHttpRequest().getDecodedFormParameters().getFirst("smsCode")`, validation fails with an empty input error.
- **Lost CSRF Session Tokens**: Custom authenticators often expect hidden session state tokens. Always verify whether the SPI requires rendering additional hidden fields like `<input type="hidden" name="execution" value={...} />`.

## Summary

Custom Keycloak SPIs do not have to rely on basic FreeMarker forms. By extending `KcContextExtensionPerPage` and rendering split-digit form components bound to standard form submissions, enterprise applications can offer polished SMS OTP workflows without altering Java backend logic.

## Further Reading

- [Keycloak Authentication SPI Documentation](https://www.keycloak.org/docs/latest/server_development/#_auth_spi)
- [Keycloakify Custom Pages Guide](https://docs.keycloakify.dev/custom-pages)
- [Web Form Accessibility for OTP Inputs](https://www.w3.org/WAI/tutorials/forms/)

Connect your custom SMS authenticators to Keycloakify to build fast, secure multi-factor authentication views.
