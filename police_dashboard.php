<?php
session_start();
if(!isset($_SESSION['police_id'])){
    header("Location: index.php");
    exit();
}
include 'db.php';

// Fetch monthly fines stats
$stats = [];
$res = mysqli_query($conn, "
    SELECT DATE_FORMAT(created_at,'%Y-%m') AS month,
           COUNT(*) as total_cases,
            SUM(fine_amount) AS total_fines
    FROM violations
    GROUP BY month
    ORDER BY month DESC
    LIMIT 6
");

while($row = mysqli_fetch_assoc($res)){
    $stats[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Police Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom CSS -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .btn-logout {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }
        
        .stats-card {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(44, 62, 80, 0.3);
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .slide-in {
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .pulse-effect {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(52, 152, 219, 0); }
            100% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0); }
        }
        
        .table-row-hover:hover {
            background-color: rgba(52, 152, 219, 0.1);
            transition: background-color 0.2s ease;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Header -->
    <header class="glass-effect shadow-lg sticky top-0 z-10">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-600 text-white p-3 rounded-full">
                    <i class="fas fa-shield-alt text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Police Dashboard</h1>
            </div>
            <a href="logout.php" class="btn-logout text-white px-4 py-2 rounded-lg font-medium flex items-center space-x-2">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <!-- Upload plate section -->
        <div class="glass-effect rounded-xl p-6 mb-8 card-hover fade-in">
            <div class="flex items-center mb-4">
                <div class="bg-blue-100 p-3 rounded-full mr-3">
                    <i class="fas fa-camera text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800">Upload Number Plate</h3>
            </div>
            <form id="uploadForm" enctype="multipart/form-data" method="POST" action="upload_plate.php" class="space-y-4">
                <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                    <div class="relative flex-grow">
                        <input type="file" name="plate_image" accept="image/*" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary px-6 py-3 rounded-lg font-medium flex items-center justify-center space-x-2 pulse-effect">
                        <i class="fas fa-upload"></i>
                        <span>Upload & Scan</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Vehicle + Owner Details -->
        <div class="glass-effect rounded-xl p-6 mb-8 card-hover fade-in" id="detailsCard" style="display:none;">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 p-3 rounded-full mr-3">
                    <i class="fas fa-car text-green-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800">Vehicle & Owner Details</h3>
            </div>
            <div id="ownerDetails" class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
                <p class="text-center text-gray-600 mt-2">Loading vehicle details...</p>
            </div>
            <form id="fineForm" method="POST" action="send_fine.php" class="space-y-4">
                <input type="hidden" name="plate_number" id="plate_number">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Crime:</label>
                        <select name="crime" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
                            <option value="">-- Select Crime --</option>
                            <option value="Speeding|500">Speeding - ₹500</option>
                            <option value="Signal Jump|1000">Signal Jump - ₹1000</option>
                            <option value="No Helmet|300">No Helmet - ₹300</option>
                            <option value="Drunk Driving|2000">Drunk Driving - ₹2000</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Location:</label>
                        <input type="text" name="location" placeholder="Enter location of violation" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Notes:</label>
                    <textarea name="notes" placeholder="Additional notes (optional)" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"></textarea>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary px-6 py-3 rounded-lg font-medium flex items-center space-x-2">
                        <i class="fas fa-paper-plane"></i>
                        <span>Send Fine Notice</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Dashboard Stats -->
        <div class="glass-effect rounded-xl p-6 card-hover fade-in">
            <div class="flex items-center mb-6">
                <div class="bg-purple-100 p-3 rounded-full mr-3">
                    <i class="fas fa-chart-bar text-purple-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800">Monthly Fine Summary</h3>
            </div>
            
            <div class="overflow-x-auto rounded-lg">
                <table class="w-full text-left">
                    <thead class="bg-gray-100 text-gray-700 uppercase">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Month</th>
                            <th class="px-6 py-3 font-semibold">Total Cases</th>
                            <th class="px-6 py-3 font-semibold">Total Fines (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach($stats as $index => $s): ?>
                        <tr class="table-row-hover slide-in" style="animation-delay: <?= $index * 0.1 ?>s;">
                            <td class="px-6 py-4 font-medium text-gray-800"><?= htmlspecialchars($s['month']) ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-blue-100 text-blue-800 font-medium">
                                    <?= htmlspecialchars($s['total_cases']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-green-600">₹<?= htmlspecialchars($s['total_fines'] ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div class="stats-card">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-blue-200">Total Cases</p>
                            <h4 class="text-2xl font-bold mt-1">
                                <?php 
                                    $total_cases = array_sum(array_column($stats, 'total_cases'));
                                    echo $total_cases;
                                ?>
                            </h4>
                        </div>
                        <div class="bg-blue-500 p-3 rounded-full">
                            <i class="fas fa-clipboard-list text-white text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stats-card">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-blue-200">Total Fines</p>
                            <h4 class="text-2xl font-bold mt-1">
                                ₹<?php 
                                    $total_fines = array_sum(array_column($stats, 'total_fines'));
                                    echo $total_fines;
                                ?>
                            </h4>
                        </div>
                        <div class="bg-green-500 p-3 rounded-full">
                            <i class="fas fa-rupee-sign text-white text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stats-card">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-blue-200">Avg. per Case</p>
                            <h4 class="text-2xl font-bold mt-1">
                                ₹<?php 
                                    $avg_fine = $total_cases > 0 ? round($total_fines / $total_cases, 2) : 0;
                                    echo $avg_fine;
                                ?>
                            </h4>
                        </div>
                        <div class="bg-yellow-500 p-3 rounded-full">
                            <i class="fas fa-chart-line text-white text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // After OCR, upload_plate.php can redirect back with ?plate=XYZ123
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.has('plate')){
            let plate = urlParams.get('plate');
            let detailsCard = document.getElementById('detailsCard');
            detailsCard.style.display = 'block';
            detailsCard.classList.add('animate__animated', 'animate__fadeInUp');
            
            document.getElementById('plate_number').value = plate;

            fetch("fetch_owner.php?plate=" + plate)
                .then(res=>res.text())
                .then(data=>{
                    document.getElementById('ownerDetails').innerHTML = data;
                });
        }
        
        // Add animation to cards on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.fade-in');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            cards.forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>