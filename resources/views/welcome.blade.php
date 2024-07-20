<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidding</title>
</head>
<body>
    
    <table id="table-bid" border="1" style="font-size: 78px">
        <tr>
            <td>NAME</td>
            <td>PRICE</td>
        </tr>
    </table>

<script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
<script>
    const APP_KEY = "hgjmx3ahkhzkeirkjodc";
    const CHANNEL_NAME = "bid-placed";
    const EVENT_NAME = "App\\Events\\BidPlacedEvent";

    const pusher = new Pusher(APP_KEY, {
        cluster: "",
        enabledTransports: ['ws'],
        forceTLS:false,
        wsHost: "127.0.0.1",
        wsPort: "8080"
    });

    const channel = pusher.subscribe(CHANNEL_NAME);

    channel.bind(EVENT_NAME, (data) => {
        const tableBid = document.getElementById('table-bid');

        const row = tableBid.insertRow();

        const cell1 = row.insertCell(0);
        const cell2 = row.insertCell(1);
        cell1.innerHTML = data.name;
        cell2.innerHTML = data.price;
    });
</script>
</body>
</html>