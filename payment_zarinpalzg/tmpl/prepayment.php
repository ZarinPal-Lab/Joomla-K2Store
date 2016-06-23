<?php defined('_JEXEC') or die('Restricted access'); ?>
<div class="note">
    <?php echo JText::_("PLG_K2STORE_PAYMENTS_ZARINPALZG_DISPLAY_NAME"); ?>
    <br/>
    <br/>
    <?php echo JText::_("PLG_K2STORE_PAYMENTS_ZARINPALZG_PREPARE_MESSAGE"); ?>
</div>
<br/>
<div class="note">
    <?php if (!isset($vars->error)) { ?>
        <form action="<?php echo $vars->redirectToZP ?>" method="get">
            <input type="submit" class="btn btn-primary button"
                   value="<?php echo JText::_("PLG_K2STORE_PAYMENTS_ZARINPALZG_PAY"); ?>"/>
        </form>
    <?php } else { ?>
        <p style="color:red; font-weight: bold; border: 3px darkred solid; border-radius: 10px;line-height: 25px;padding:10px;margin:10px;background-color: rgba(255, 230, 230, 0.56)">
            <?php echo JText::_("PLG_K2STORE_PAYMENTS_ZARINPALZG_ERROR_TITLE"); ?>
            <br>
            <?php echo $vars->error; ?>
        </p>
    <?php } ?>
</div>