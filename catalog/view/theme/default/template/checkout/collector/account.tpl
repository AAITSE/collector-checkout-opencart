<div id="collector-account" class="row">
    <div class="well">
        <?php echo sprintf($view->__('Returning customer? Click <a class="login-link" href="%s">here</a> to login'), $view->url('account/login')); ?>
        <script>
            $(document).on('click', 'a.login-link', function(e) {
                e.preventDefault();
                $('#login-form').slideDown();
                return false;
            });
        </script>

        <div id="login-form" style="display: none;">
            <p><strong><?php echo $view->__('If you have an account with us, please log in.'); ?></strong></p>
            <form id="account-login" action="<?php echo $view->url('account/login'); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="control-label" for="input-email">
                        <?php echo $view->__('E-Mail Address'); ?>
                    </label>
                    <input type="text" name="email" value="" placeholder="<?php echo $view->__('E-Mail Address'); ?> " id="input-email" class="form-control">
                </div>

                <div class="form-group">
                    <label class="control-label" for="input-password">
                        <?php echo $view->__('Password'); ?>
                    </label>
                    <input type="password" name="password" value="" placeholder="<?php echo $view->__('Password'); ?>" id="input-password" class="form-control">

                    <a href="<?php echo $view->url('account/forgotten'); ?>">
                        <?php echo $view->__('Forgotten Password'); ?>
                    </a>
                </div>
                <input type="submit" value="Login" class="btn btn-primary">
                <input type="hidden" name="redirect" value="<?php ?>">
            </form>
        </div>
    </div>
</div>