$ErrorActionPreference = 'Stop'

try {
    Write-Host "Logging in..."
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/login" -Method POST -Body (@{ email = "john@example.com"; password = "password" } | ConvertTo-Json) -ContentType "application/json"
    $token = $response.data.token
    Write-Host "Token: $token"

    Write-Host "Creating Order..."
    $orderBody = @{ product_id = 1; quantity = 1 } | ConvertTo-Json
    $order = Invoke-RestMethod -Uri "http://localhost:8002/api/orders" -Method POST -Headers @{ Authorization = "Bearer $token" } -Body $orderBody -ContentType "application/json"
    Write-Host "Order ID: $($order.data.id)"

    Write-Host "Sleeping for 5 seconds for queue..."
    Start-Sleep -Seconds 5

    Write-Host "Fetching User Orders..."
    $orders = Invoke-RestMethod -Uri "http://localhost:8002/api/orders/user/1" -Headers @{ Authorization = "Bearer $token" }
    Write-Host "Orders for User 1: $($orders.data.Count)"

    Write-Host "Fetching Notifications..."
    $notifs = Invoke-RestMethod -Uri "http://localhost:8003/api/notifications"
    Write-Host "Notifs Count: $($notifs.data.data.Count)"

    if ($notifs.data.data.Count -gt 0) {
        $notifId = $notifs.data.data[0].id
        Write-Host "Marking Notification $notifId as read..."
        $read = Invoke-RestMethod -Uri "http://localhost:8003/api/notifications/$notifId/read" -Method PUT
        Write-Host "Notif Read At: $($read.data.read_at)"
    } else {
        Write-Host "No notifications found!"
    }
} catch {
    Write-Host "Error: $_"
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $reader.BaseStream.Position = 0
        $reader.DiscardBufferedData()
        $responseBody = $reader.ReadToEnd()
        Write-Host "Response Body: $responseBody"
    }
}
