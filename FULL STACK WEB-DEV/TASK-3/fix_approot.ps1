$base = 'c:\Users\khaja\Desktop\programs\Apex-Planet-internship\FULL STACK WEB-DEV\TASK-3'
$files = Get-ChildItem -Path $base -Recurse -Filter '*.php'
foreach ($f in $files) {
    $content = Get-Content $f.FullName -Raw -Encoding UTF8
    $search  = "define('APP_ROOT', '/task3/');"
    if ($content -and $content.Contains($search)) {
        $updated = $content.Replace($search, '')
        Set-Content -Path $f.FullName -Value $updated -Encoding UTF8 -NoNewline
        Write-Host "Fixed: $($f.Name)"
    }
}
Write-Host "Done."
