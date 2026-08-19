$hook = Join-Path $PSScriptRoot 'rtk-auto-allow.mjs'
$cases = @(
  @{ cmd = 'git status';                   want = 'allow' },
  @{ cmd = 'ls -al';                       want = 'allow' },
  @{ cmd = 'php artisan test';             want = 'allow' },
  @{ cmd = 'git push --force origin main'; want = 'ask'   },
  @{ cmd = 'php artisan migrate:fresh';    want = 'ask'   },
  @{ cmd = 'rm -rf foo';                   want = 'none'  }
)

$fail = 0
foreach ($c in $cases) {
  $json = '{"tool_name":"Shell","tool_input":{"command":"' + $c.cmd + '"}}'
  $res = $json | node $hook | ConvertFrom-Json
  $got = if ($res.permission) { $res.permission } else { 'none' }
  $rw = if ($res.updated_input) { $res.updated_input.command } else { '(no rewrite)' }
  $mark = if ($got -eq $c.want) { 'OK  ' } else { $fail++; 'FAIL' }
  Write-Output ("{0} {1,-30} -> {2,-5} {3}" -f $mark, $c.cmd, $got, $rw)
}

if ($fail -gt 0) {
  Write-Output "FAILURES: $fail"
  exit 1
}
Write-Output 'all cases pass'
