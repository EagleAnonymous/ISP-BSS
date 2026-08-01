$content = Get-Content 'C:\xampp\htdocs\BSS\resources\views\subscriber\dashboard.blade.php' -Raw
$pattern = '(?s)\n\s*<a href="\{\{ route\(''subscriber\.dashboard''\) \}\}".*?>Support\s*</span>\s*</a>\n*'
$newContent = $content -replace $pattern, "
"
Set-Content -Path 'C:\xampp\htdocs\BSS\resources\views\subscriber\dashboard.blade.php' -Value $newContent -NoNewline
Write-Host "Done"
