# Pre-commit bundle: classify style + build commit URL + strip trailers
# Usage: powershell -NoProfile -ExecutionPolicy Bypass -File pre-commit-bundle.ps1 -RepoPath . [-Force]
# Prints JSON: {"style":"conventional","url":"https://...","trailers_stripped":2}
# Never throws, never exits non-zero.
param(
    [string]$RepoPath = '.',
    [switch]$Force
)

$result = @{
    style = 'conventional'
    url = ''
    trailers_stripped = 0
    trailers_status = 'clean'
}

try {
    $ErrorActionPreference = 'Stop'
    
    # === CLASSIFY ===
    $fallback = 'conventional'
    $verbs = 'add|update|remove|fix|bump|revert|use|move|change|make|set|allow|improve|clean|rename|apply|refactor|tweak|format|adjust|simplify|delete|drop|ignore'
    
    $gitDir = git -C $RepoPath rev-parse --git-dir 2>$null
    if ($LASTEXITCODE -eq 0 -and $gitDir) {
        $cacheFile = Join-Path $gitDir 'info\commit-style'
        if (-not $Force -and (Test-Path $cacheFile)) {
            $cached = Get-Content $cacheFile -ErrorAction SilentlyContinue
            if ($cached -match '^(conventional|imperative-caps|imperative-lower)$') {
                $result.style = $cached
            }
        }
        
        if ($result.style -eq $fallback -or $Force) {
            $subjects = git -C $RepoPath log -50 --format=%s 2>$null
            if ($LASTEXITCODE -eq 0 -and $subjects) {
                $s = @($subjects | Where-Object { $_ -and $_ -notmatch '^Merge (pull request|branch)\b' })
                if ($s.Count -ge 5) {
                    $n = $s.Count
                    $conventional = @($s | Where-Object { $_ -cmatch '^[a-z]+(\([^)]+\))?!?: ' }).Count
                    if ($conventional / $n -ge 0.4) { $result.style = 'conventional' }
                    else {
                        $capsVerbs = ($verbs -split '\|' | ForEach-Object { $_.Substring(0, 1).ToUpper() + $_.Substring(1) }) -join '|'
                        $caps = @($s | Where-Object { $_ -cmatch "^($capsVerbs) " }).Count
                        if ($caps / $n -ge 0.4) { $result.style = 'imperative-caps' }
                        else {
                            $lower = @($s | Where-Object { $_ -cmatch "^($verbs) " }).Count
                            if ($lower / $n -ge 0.4) { $result.style = 'imperative-lower' }
                        }
                    }
                    
                    if ($gitDir -and $cacheFile) {
                        $infoDir = Split-Path $cacheFile
                        if (-not (Test-Path $infoDir)) {
                            New-Item -ItemType Directory -Force -Path $infoDir | Out-Null
                        }
                        Set-Content -NoNewline -Path $cacheFile -Value $result.style -ErrorAction SilentlyContinue
                    }
                }
            }
        }
    }
    
    # === COMMIT-URL ===
    $remote = (git -C $RepoPath remote get-url origin 2>$null)
    if ($LASTEXITCODE -eq 0 -and $remote) {
        $remote = $remote.Trim()
        $branch = (git -C $RepoPath rev-parse --abbrev-ref HEAD 2>$null)
        if ($LASTEXITCODE -eq 0 -and $branch) {
            $branch = $branch.Trim()
            $clean = $remote -replace '\.git/?$', '' -replace '/$', ''
            
            if ($clean -match '^[^@/]+@([^:]+):(.+)$') {
                $host_ = $Matches[1]; $path = $Matches[2]
            }
            elseif ($clean -match '^[a-z+]+://(?:[^@/]+@)?([^/]+)/(.+)$') {
                $host_ = $Matches[1]; $path = $Matches[2]
            }
            else {
                $host_ = $null; $path = $null
            }
            
            if ($host_ -and $path) {
                $host_ = $host_ -replace ':\d+$', ''
                $path = $path.Trim('/')
                $url = "https://$host_/$path/commits/$branch/"
                $result.url = $url
                
                $batDir = Join-Path $RepoPath '.cursor\skills\git-push'
                if (Test-Path $batDir) {
                    $bat = "@echo off`r`nstart `"`" `"$url`"`r`n"
                    [System.IO.File]::WriteAllText((Join-Path $batDir 'open-commits.bat'), $bat, (New-Object System.Text.ASCIIEncoding))
                }
            }
        }
    }
    
    # === STRIP-TRAILERS ===
    $aiNames = 'Cursor|Claude|Copilot|Devin|Codex|Aider|Windsurf|Cody'
    $patterns = @(
        "^Co-authored-by:.*\b($aiNames)\b",
        '^Co-authored-by:.*@(cursor\.com|anthropic\.com)',
        '^(Generated with|Assisted-by:)',
        '^.{0,4}Generated with'
    )
    
    if (-not $gitDir) {
        $gitDir = (git -C $RepoPath rev-parse --absolute-git-dir 2>$null)
    }
    
    if ($LASTEXITCODE -eq 0 -and $gitDir) {
        $skipReason = $null
        foreach ($marker in 'MERGE_HEAD', 'rebase-merge', 'rebase-apply') {
            if (Test-Path (Join-Path $gitDir $marker)) { $skipReason = 'merge in progress'; break }
        }
        
        if (-not $skipReason) {
            $msg = git -C $RepoPath log -1 --pretty=%B 2>$null
            if ($LASTEXITCODE -eq 0 -and $msg) {
                $remoteBranch = git -C $RepoPath branch -r --contains HEAD 2>$null
                if ($LASTEXITCODE -eq 0 -and $remoteBranch) {
                    $skipReason = 'already pushed'
                }
                else {
                    $lines = @($msg -split "`r?`n")
                    $kept = @($lines | Where-Object {
                        $line = $_
                        -not ($patterns | Where-Object { $line -imatch $_ })
                    })
                    $removed = $lines.Count - $kept.Count
                    
                    if ($removed -gt 0) {
                        while ($kept.Count -gt 0 -and $kept[-1].Trim() -eq '') {
                            $kept = $kept[0..($kept.Count - 2)]
                        }
                        
                        if ($kept.Count -gt 0) {
                            $tmp = Join-Path $env:TEMP ('striptrailers-' + [guid]::NewGuid() + '.txt')
                            $utf8 = New-Object System.Text.UTF8Encoding $false
                            [System.IO.File]::WriteAllText($tmp, ($kept -join "`n"), $utf8)
                            git -C $RepoPath commit -q --amend --allow-empty -F $tmp 2>$null | Out-Null
                            $code = $LASTEXITCODE
                            Remove-Item $tmp -Force -ErrorAction SilentlyContinue
                            
                            if ($code -eq 0) {
                                $result.trailers_stripped = $removed
                                $result.trailers_status = "stripped $removed"
                            }
                            else {
                                $skipReason = 'amend failed'
                            }
                        }
                        else {
                            $skipReason = 'would empty message'
                        }
                    }
                }
            }
            else {
                $skipReason = 'no commits'
            }
        }
        
        if ($skipReason) {
            $result.trailers_status = "skipped: $skipReason"
        }
    }
    else {
        $result.trailers_status = 'skipped: not a repo'
    }
}
catch {
    # All failures handled gracefully above
}

$result | ConvertTo-Json -Compress
exit 0
