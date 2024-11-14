<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- Tambahkan ini -->
    <title>Real-Time Bidding with SSE</title>
</head>
<body>
    <h1>Real-Time Bidding</h1>
    <!-- Form untuk mengirim bid -->
    <form id="bidForm">
        <input type="text" id="name" placeholder="Your name" required>
        <input type="number" id="price" placeholder="Bid price" required>
        <button type="submit">Submit Bid</button>
    </form>

    <h2>Bids</h2>
    <!-- Tabel untuk menampilkan bid -->
    <table border="1" id="bidTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <!-- Row bid akan di-append di sini -->
        </tbody>
    </table>

    <script>
        // Mendengarkan event stream dari server melalui SSE
        const eventSource = new EventSource('/sse/stream');
        
        eventSource.onmessage = function(event) {
            const bids = JSON.parse(event.data);
            const bidTableBody = document.querySelector('#bidTable tbody');

            // Bersihkan tabel sebelum append
            bidTableBody.innerHTML = '';

            // Append semua bid ke tabel
            bids.forEach(bid => {
                const row = document.createElement('tr');
                row.innerHTML = `<td>${bid.name}</td><td>${bid.price}</td>`;
                bidTableBody.appendChild(row);
            });
        };

        // Mengirim bid ke server melalui HTTP POST
        document.getElementById('bidForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('name').value;
            const price = document.getElementById('price').value;

            // Kirimkan data bid ke server
            fetch('/sse/bid', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Pastikan ini ada
                },
                body: JSON.stringify({ name: name, price: price })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Bid submitted:', data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
