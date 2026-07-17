# Examples — system-study-packet

## Example invocation

```
Create system study packet for examples/kindly-e-commerce-1122.
Learner: intermediate. Output under docs/study-packets/.
```

Expected files:

- `docs/study-packets/kindly-ecommerce-decomposition.md`
- `docs/study-packets/kindly-ecommerce-8-principle-study.md`

## Reference implementation (this monorepo)

| File | Path |
|------|------|
| Decomposition | `examples/kindly-e-commerce-1122/docs/study-packets/decomposition-kindly-ecommerce.md` |
| 8-principle | `examples/kindly-e-commerce-1122/docs/study-packets/8-principle-study-kindly-ecommerce.md` |
| Handoff | `examples/kindly-e-commerce-1122/docs/NEXT_SESSION.md` |
| Routes | `examples/kindly-e-commerce-1122/routes/web.php` |

## Slug conventions

| Project folder | Slug |
|----------------|------|
| `kindly-e-commerce-1122` | `kindly-ecommerce` |
| `kindly-login-1122` | `kindly-login` |
| `booking-v1` | `booking-v1` |

## Regenerate after feature work

```
Update system study packet for kindly-e-commerce — refresh status matrix and test count only.
```

Agent should re-run tests, diff routes/services, update both MD files minimally.
