$uri = "http://127.0.0.1:8081/inventory/dashboard"
try {
    $r = Invoke-WebRequest -Uri $uri -UseBasicParsing -MaximumRedirection 10
    Write-Output "Status: $($r.StatusCode)"
} catch [System.Net.WebException] {
    if ($_.Exception.Response) {
        $code = [int]$_.Exception.Response.StatusCode
        Write-Output "Status: $code"
    } else {
        Write-Output "Error: $($_.Exception.Message)"
    }
} catch {
    Write-Output "Error: $($_.Exception.Message)"
}
