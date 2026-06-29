<?php
require_once 'site_common.php';
$posts = [
    'choose-solar-panel' => [
        'icon' => 'fa-solar-panel',
        'title' => 'How to choose a solar panel',
        'intro' => 'Compare wattage, efficiency, warranty and roof space before buying.',
        'body' => 'Choose solar panels by checking panel wattage, efficiency, warranty period, available roof space, and your daily electricity use. For homes in Nepal, also consider roof direction, shade from nearby buildings, and the total load you want to support.'
    ],
    'battery-backup-basics' => [
        'icon' => 'fa-car-battery',
        'title' => 'Battery backup basics',
        'intro' => 'Understand capacity, voltage and daily energy needs for backup systems.',
        'body' => 'Battery backup size depends on how many appliances you want to run and for how many hours. Check battery voltage, amp-hour capacity, depth of discharge, and compatibility with your inverter before buying.'
    ],
    'inverter-buying-guide' => [
        'icon' => 'fa-bolt',
        'title' => 'Inverter buying guide',
        'intro' => 'Pick the right inverter size for appliances, surge load and battery support.',
        'body' => 'Your inverter should handle your normal running load and temporary surge load. A hybrid inverter is useful when you want solar charging, grid charging, and battery backup in one system.'
    ]
];
$slug = $_GET['post'] ?? '';
$post = $posts[$slug] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php renderHeader('blog'); ?>
<section class="page-header">
    <h1>Solar Blog</h1>
    <p>Helpful buying guides and renewable energy tips for customers.</p>
</section>
<?php if ($post): ?>
<section class="section-p1">
    <article class="card blog-card" style="max-width:900px;margin:auto;">
        <div class="blog-img"><i class="fa <?php echo e($post['icon']); ?>"></i></div>
        <h2><?php echo e($post['title']); ?></h2>
        <p><?php echo e($post['intro']); ?></p>
        <p><?php echo e($post['body']); ?></p>
        <a class="outline-btn" href="blog.php">Back to Blog</a>
        <a class="primary-btn" href="shop.php">Shop Products</a>
    </article>
</section>
<?php else: ?>
<section class="section-p1 blog-grid">
    <?php foreach ($posts as $key => $item): ?>
    <article class="card blog-card">
        <div class="blog-img"><i class="fa <?php echo e($item['icon']); ?>"></i></div>
        <h3><?php echo e($item['title']); ?></h3>
        <p><?php echo e($item['intro']); ?></p>
        <a class="outline-btn" href="blog.php?post=<?php echo urlencode($key); ?>">Read More</a>
    </article>
    <?php endforeach; ?>
</section>
<?php endif; ?>
<?php renderFooter(); ?>
<script src="script.js"></script>
</body>
</html>
