<?php
/** @var ModelCollectorView $view */
/** @var string $message */
/** @var string $breadcrumbs */
/** @var string $header */
/** @var string $footer */
/** @var string $breadcrumbs */
/** @var string $column_left */
/** @var string $column_right */
/** @var string $content_top */
/** @var string $content_bottom */
?>

<!-- Start header -->
<?php echo $header; ?>
<!-- End header -->

    <div class="container">
        <!-- Start breadcrumb -->
        <ul class="breadcrumb">
            <?php foreach ($breadcrumbs as $breadcrumb): ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
            <?php endforeach; ?>
        </ul>
        <!-- End breadcrumb -->

        <div class="row">
            <?php echo $column_left; ?>

            <?php if ($column_left && $column_right): ?>
                <?php $class = 'col-sm-6'; ?>
            <?php elseif ($column_left || $column_right): ?>
                <?php $class = 'col-sm-9'; ?>
            <?php else: ?>
                <?php $class = 'col-sm-12'; ?>
            <?php endif; ?>

            <div id="content" class="<?php echo $class; ?>">
                <?php echo $content_top; ?>

                <!-- Start Content -->
                <h1><?php echo $view->__('Error'); ?></h1>

                <?php echo $message; ?>

                <?php echo $content_bottom; ?>
                <!-- End Content -->
            </div>
            <?php echo $column_right; ?>
        </div>
    </div>

<!-- Start footer -->
<?php echo $footer; ?>
<!-- Start footer -->
