<?php defined('_JEXEC') or die('Restricted access'); ?>
<div class="note">
    <?php if (isset($vars->error)) { ?>
        <p style="color:red; font-weight: bold; border: 3px darkred solid; border-radius: 10px;line-height: 25px;padding:10px;margin:10px;background-color: rgba(255, 230, 230, 0.56)">
            <?php echo JText::_("PLG_K2STORE_PAYMENTS_ZARINPALZG_ERROR_TITLE"); ?>
            <br>
            <?php echo $vars->error; ?>
        </p>
    <?php } else { ?>
        <img alt="zarinpal payment"
             src="<?php echo JURI::base(true) . '/plugins/k2store/payment_zarinpalzg/payment_zarinpalzg/img/zarinpal.png'; ?>"
             width="100" style="float:right"/>
        <p style="line-height:20px;color: darkgreen;display:inline-block;margin-right: 15px;">
            <?php echo JText::_("PLG_K2STORE_PAYMENTS_ZARINPALZG_PAYMENT_SUCCEED"); ?>
            <br>
            <span><?php echo JText::_("PLG_K2STORE_PAYMENTS_ZARINPALZG_REF_ID"); ?></span>
            <strong style="padding-right:10px"><?php echo $vars->RefID; ?></strong>
            <br>
            <span><?php echo JText::_("PLG_K2STORE_PAYMENTS_ZARINPALZG_ORDER_ID"); ?></span>
            <strong style="padding-right:10px"><?php echo $vars->orderID; ?></strong>
            <span style="padding-right:10px">(&nbsp;<a href="<?php echo JURI::root() . 'index.php/component/k2store/orders/view/' . $vars->id; ?>">
                    <?php echo JText::_("PLG_K2STORE_PAYMENTS_ZARINPALZG_SEE_ORDER"); ?>
                </a>&nbsp;)</span>
        </p>
    <?php } ?>
</div>