# install.ps1 - Install win-disk-cleaner skill for Claude Code (Windows)
# Usage: irm https://raw.githubusercontent.com/orzcls/win-disk-cleaner/main/install.ps1 | iex

$ErrorActionPreference = "Stop"
$SkillName = "win-disk-cleaner"
$RepoZip = "https://github.com/orzcls/win-disk-cleaner/archive/refs/heads/main.zip"

# Determine install target
if (Test-Path ".claude\skills") {
    $Target = ".claude\skills\$SkillName"
} elseif (Test-Path "$env:USERPROFILE\.claude\skills") {
    $Target = "$env:USERPROFILE\.claude\skills\$SkillName"
} else {
    $Target = "$env:USERPROFILE\.claude\skills\$SkillName"
    New-Item -ItemType Directory -Path "$env:USERPROFILE\.claude\skills" -Force | Out-Null
}

Write-Host "==> Installing $SkillName skill..." -ForegroundColor Cyan
Write-Host "    Target: $Target"

# Download and extract
$TempZip = "$env:TEMP\$SkillName.zip"
$TempDir = "$env:TEMP\$SkillName-extract"

Invoke-WebRequest -Uri $RepoZip -OutFile $TempZip
if (Test-Path $TempDir) { Remove-Item -Recurse -Force $TempDir }
Expand-Archive -Path $TempZip -DestinationPath $TempDir -Force

# Find extracted folder (GitHub adds "-main" suffix)
$Extracted = Get-ChildItem $TempDir | Select-Object -First 1

# Copy skill files (only SKILL.md, scripts/, references/)
if (Test-Path $Target) { Remove-Item -Recurse -Force $Target }
New-Item -ItemType Directory -Path $Target -Force | Out-Null

Copy-Item "$($Extracted.FullName)\SKILL.md" "$Target\SKILL.md"
if (Test-Path "$($Extracted.FullName)\scripts") {
    Copy-Item "$($Extracted.FullName)\scripts" "$Target\scripts" -Recurse
}
if (Test-Path "$($Extracted.FullName)\references") {
    Copy-Item "$($Extracted.FullName)\references" "$Target\references" -Recurse
}

# Cleanup
Remove-Item -Force $TempZip
Remove-Item -Recurse -Force $TempDir

Write-Host ""
Write-Host "==> Done! Skill '$SkillName' installed." -ForegroundColor Green
Write-Host "    Location: $Target"
Write-Host "    Restart Claude Code to load the new skill."
Write-Host ""
Get-ChildItem $Target -Recurse -File | ForEach-Object {
    Write-Host "    $($_.FullName)" -ForegroundColor Gray
}
