<?php
require_once __DIR__ . '/../backend/includes/config.php';
require_once __DIR__ . '/../backend/includes/data.php';

$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$item  = $id ? find_news($id) : null;
$loai  = $_GET['loai'] ?? 'all';
if ($loai !== 'all' && !isset($news_types[$loai])) $loai = 'all';

$pageTitle = $item ? $item['tieu_de'] : 'Tin tức và khuyến mãi';
$pageDesc  = $item ? $item['tom_tat'] : 'Tin tức, sự kiện, khuyến mãi và thông báo mới nhất từ nhà xe Tài Nguyên.';
include __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1><?= $item ? 'Chi tiết tin' : 'Tin tức và khuyến mãi' ?></h1>
        <p>Tin tức, sự kiện, khuyến mãi và thông báo mới từ Tài Nguyên.</p>
    </div>
</section>

<section class="section">
    <div class="wrap">

<?php if ($item): ?>
        <article class="article">
            <p>
                <span class="tag tag-<?= e($item['loai']) ?>"><?= e($news_types[$item['loai']]) ?></span>
                <span class="date"><?= date('d/m/Y', strtotime($item['ngay'])) ?></span>
            </p>
            <h2><?= e($item['tieu_de']) ?></h2>
            <?php foreach ($item['noi_dung'] as $para): ?>
                <p><?= e($para) ?></p>
            <?php endforeach; ?>
            <p style="margin-top:32px">
                <a class="btn btn-line" href="tin-tuc.php">Quay lại danh sách</a>
                <?php if ($item['loai'] === 'khuyen-mai'): ?>
                    <a class="btn btn-y" href="dat-ve.php">Đặt vé ngay</a>
                <?php endif; ?>
            </p>
        </article>

<?php elseif ($id): ?>
        <div class="alert alert-bad"><p>Không tìm thấy bài viết này. <a href="tin-tuc.php">Xem tất cả tin</a>.</p></div>

<?php else:
    $list = array_values(array_filter($news, function ($n) use ($loai) { return $loai === 'all' || $n['loai'] === $loai; }));
?>
        <div class="toolbar">
            <div class="chips" role="navigation" aria-label="Lọc theo loại tin">
                <a class="chip<?= $loai === 'all' ? ' is-on' : '' ?>" href="tin-tuc.php">Tất cả</a>
                <?php foreach ($news_types as $key => $label): ?>
                    <a class="chip<?= $loai === $key ? ' is-on' : '' ?>" href="tin-tuc.php?loai=<?= e($key) ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!$list): ?>
            <p class="empty">Chưa có bài viết nào trong mục này.</p>
        <?php else: ?>
            <ul class="news-list" style="max-width:820px">
                <?php foreach ($list as $n): ?>
                    <li>
                        <a href="tin-tuc.php?id=<?= (int)$n['id'] ?>">
                            <span class="tag tag-<?= e($n['loai']) ?>"><?= e($news_types[$n['loai']]) ?></span><span class="date"><?= date('d/m/Y', strtotime($n['ngay'])) ?></span>
                            <h3><?= e($n['tieu_de']) ?></h3>
                            <p class="muted" style="margin:0"><?= e($n['tom_tat']) ?></p>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
<?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
