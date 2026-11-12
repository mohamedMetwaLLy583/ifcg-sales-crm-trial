param(
 [string]$PhpPath = 'C:\Users\Right Solutions\.codex\visualizations\2026\09\13\01a09cfe-cd15-7931-8b04-3bb39d160147\phase0-php\php.exe',
 [int]$Port = 8197
)
$ErrorActionPreference = 'Stop'
if (-not (Test-Path -LiteralPath $PhpPath)) { throw 'PHP not found. Provide -PhpPath with your PHP executable.' }
Push-Location (Join-Path $PSScriptRoot 'public')
try {
 Write-Host "IFCG demo: http://127.0.0.1:$Port/"
 & $PhpPath -d upload_max_filesize=24M -d post_max_size=32M -S "127.0.0.1:$Port" '../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'
} finally { Pop-Location }

