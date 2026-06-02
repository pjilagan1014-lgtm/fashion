<?php
$con = mysqli_connect("localhost", "root", "", "fashion");
include('auth_check.php');

// 1. Calculate Comprehensive Revenue from the payments table
$revenue_query = mysqli_query($con, "
    SELECT 
        SUM(amount) as total_sales,
        COUNT(id) as total_successful_payments,
        -- Breakdown by category
        SUM(CASE WHEN booking_id IS NOT NULL THEN amount ELSE 0 END) as gown_revenue,
        SUM(CASE WHEN makeup_bookings_id IS NOT NULL THEN amount ELSE 0 END) as makeup_revenue,
        SUM(CASE WHEN package_bookings_id IS NOT NULL THEN amount ELSE 0 END) as package_revenue
    FROM payments 
    WHERE status = 'approved'
");

$r = mysqli_fetch_assoc($revenue_query);

// 2. Count Total Bookings across all types
$gown_count = mysqli_query($con, "SELECT COUNT(*) as total FROM bookings WHERE payment_status='paid'")->fetch_assoc()['total'];
$makeup_count = mysqli_query($con, "SELECT COUNT(*) as total FROM makeup_bookings WHERE status='approved'")->fetch_assoc()['total'];
$package_count = mysqli_query($con, "SELECT COUNT(*) as total FROM package_bookings WHERE status='approved'")->fetch_assoc()['total'];

$total_bookings_count = $gown_count + $makeup_count + $package_count;
?>

<style>
    .report-container { font-family: 'Inter', sans-serif; color: #333; max-width: 600px; }
    .main-total { background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 5px solid #10b981; margin-bottom: 20px; }
    .main-total h1 { color: #10b981; margin: 0; font-size: 2rem; }
    .breakdown-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .stat-item { background: #fff; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; }
    .stat-item span { display: block; font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 600; }
    .stat-item p { font-size: 1.2rem; font-weight: 700; margin: 5px 0 0; }
</style>

<div class="report-container">
    <h2>Financial Sales Report</h2>
    <hr style="margin-bottom: 20px; border: 0; border-top: 1px solid #eee;">

    <!-- Overall Total -->
    <div class="main-total">
        <span>Total Gross Revenue</span>
        <h1>₱<?php echo number_format($r['total_sales'] ?? 0, 2); ?></h1>
        <small>Total Transactions: <?php echo $r['total_successful_payments']; ?></small>
    </div>

    <!-- Breakdown Grid -->
    <div class="breakdown-grid">
        <div class="stat-item">
            <span>Gown Rentals</span>
            <p>₱<?php echo number_format($r['gown_revenue'] ?? 0, 2); ?></p>
        </div>
        <div class="stat-item">
            <span>Makeup Services</span>
            <p>₱<?php echo number_format($r['makeup_revenue'] ?? 0, 2); ?></p>
        </div>
        <div class="stat-item">
            <span>Package Deals</span>
            <p>₱<?php echo number_format($r['package_revenue'] ?? 0, 2); ?></p>
        </div>
        <div class="stat-item">
            <span>Total Bookings</span>
            <p><?php echo $total_bookings_count; ?></p>
        </div>
    </div>
</div>