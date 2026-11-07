$files = Get-ChildItem resources/views -Recurse -Filter "*.blade.php" | Select-Object -ExpandProperty FullName
$routes = @()
foreach ($f in $files) {
    $content = Get-Content $f -Raw
    $matches = [regex]::Matches($content, 'route\(["'"'"']([^"'"'"')]+)["'"'"']\)')
    foreach ($m in $matches) {
        $routes += $m.Groups[1].Value
    }
}
$routes | Sort-Object -Unique | ForEach-Object { $_ }