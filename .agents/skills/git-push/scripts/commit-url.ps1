# Print commits-list URL for current branch. Never throws.
param([string]$RepoPath = '.')
try {
    $remote = (git -C $RepoPath remote get-url origin 2>$null)
    if ($LASTEXITCODE -ne 0 -or -not $remote) { ''; exit 0 }
    $remote = $remote.Trim()
    $branch = (git -C $RepoPath rev-parse --abbrev-ref HEAD 2>$null)
    if ($LASTEXITCODE -ne 0 -or -not $branch) { ''; exit 0 }
    $branch = $branch.Trim()
    $clean = $remote -replace '\.git/?$', '' -replace '/$', ''
    if ($clean -match '^[^@/]+@([^:]+):(.+)$') { $host_ = $Matches[1]; $path = $Matches[2] }
    elseif ($clean -match '^[a-z+]+://(?:[^@/]+@)?([^/]+)/(.+)$') { $host_ = $Matches[1]; $path = $Matches[2] }
    else { ''; exit 0 }
    $host_ = $host_ -replace ':\d+$', ''
    $path = $path.Trim('/')
    "https://$host_/$path/commits/$branch/"
} catch { '' }
exit 0
