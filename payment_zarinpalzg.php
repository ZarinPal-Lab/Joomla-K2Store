<?php
//ini_set('display_errors', 1);
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR . '/components/com_k2store/library/plugins/payment.php');
require_once(JPATH_SITE . '/components/com_k2store/helpers/utilities.php');

class plgK2StorePayment_zarinpalZG extends K2StorePaymentPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *  forcing it to be unique
     */
    var $_element = 'payment_zarinpalzg';

    public function plgK2StorePayment_zarinpalzg(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage('', JPATH_ADMINISTRATOR);
    }


    /**
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _prePayment($data)
    {
        $MerchantID = $this->params->get('merchant_id');
        $Amount = $this->zarinpalAmount($data['orderpayment_amount']);
        $Description = sprintf($this->zarinpalTranslate('payment_desc'), $data['order_id']);
        $orderInfo = $data['orderinfo'];
        $Email = $orderInfo['user_email'];
        $Mobile = $orderInfo['billing_phone_2'] ?: $orderInfo['phone_2'];
        $CallbackURL = JRoute::_(JURI::base() . 'index.php?option=com_k2store&view=checkout&task=confirmPayment&orderpayment_type=payment_zarinpalzg&order_id=' .  $data['order_id']);
        $requestContext = compact(
            'MerchantID', 'Amount', 'Description', 'Email', 'Mobile', 'CallbackURL'
        );
        $request = $this->zarinpalRequest('request', $requestContext);
        $vars = new JObject();
        $app = JFactory::getApplication();
        if (!$request) {
            $vars->error = $this->zarinpalTranslate('connection_error');
        } elseif ($request->Status == 100) {
            $prefix = (bool)$this->params->get('test_mode') ? 'sandbox' : 'www';
            $vars->redirectToZP = "https://{$prefix}.zarinpal.com/pg/StartPay/{$request->Authority}/ZarinGate";
        } else {
            $vars->error = $this->zarinpalTranslate('status_' . $request->Status);
        }

        $html = $this->_getLayout('prepayment', $vars);
        return $html;
    }

    /**
     * Processes the payment form
     * and returns HTML to be displayed to the user
     * generally with a success/failed message
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _postPayment($data)
    {
        $vars = new JObject();
        //
        $order_id = $data['order_id'];
        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_k2store/tables');
        $orderpayment = JTable::getInstance('Orders', 'Table');
        @$orderpayment->load(array('order_id' => $order_id));
        //
        try {
            if (!$orderpayment)
                throw new Exception('order_not_found');

            if ($data['Status'] != 'OK')
                throw new Exception('cancel_return', 1); // 1 == payment failed exception

            $MerchantID = $this->params->get('merchant_id');
            $Amount = $this->zarinpalAmount($orderpayment->get('orderpayment_amount'));
            $Authority = $data['Authority'];

            $verifyContext = compact('MerchantID', 'Amount', 'Authority');
            $verify = $this->zarinpalRequest('verification', $verifyContext);

            if (!$verify)
                throw new Exception('connection_error');

            $status = $verify->Status;
            if ($status != 100)
                throw new Exception('status_' . $status);

            // status == 100
            $RefID = $verify->RefID;

            $orderpayment->transaction_id = $RefID;
            $orderpayment->order_state = JText::_('K2STORE_CONFIRMED'); // CONFIRMED
            $orderpayment->order_state_id = 1; // CONFIRMED
            $orderpayment->transaction_status = 'Completed';

            $vars->RefID = $RefID;
            $vars->orderID = $order_id;
            $vars->id = $orderpayment->id;
            if ($orderpayment->save()) {
                JLoader::register('K2StoreHelperCart', JPATH_SITE . '/components/com_k2store/helpers/cart.php');
                // remove items from cart
                K2StoreHelperCart::removeOrderItems($order_id);

                // let us inform the user that the payment is successful
                require_once(JPATH_SITE . '/components/com_k2store/helpers/orders.php');
                try{
                    @K2StoreOrdersHelper::sendUserEmail(
                        $orderpayment->user_id,
                        $orderpayment->order_id,
                        $orderpayment->transaction_status,
                        $orderpayment->order_state,
                        $orderpayment->order_state_id
                    );
                } catch (Exception $e) {
                    // do nothing
                    // prevent phpMailer exception
                }
            }
        } catch (Exception $e) {
            $orderpayment->order_state = JText::_('K2STORE_PENDING'); // PENDING
            $orderpayment->order_state_id = 4; // PENDING
            if ($e->getCode() == 1) { // 1 => trnsaction canceled
                $orderpayment->order_state = JText::_('K2STORE_FAILED'); // FAILED
                $orderpayment->order_state_id = 3; //FAILED
            }
            $orderpayment->transaction_status = 'Denied';
            $orderpayment->save();
            $vars->error = $this->zarinpalTranslate($e->getMessage());
        }

        $html = $this->_getLayout('postpayment', $vars);
        return $html;
    }

    /**
     * Prepares variables for the payment form
     *
     * @return unknown_type
     */
    function _renderForm($data)
    {
        $vars = new JObject();

        $html = $this->_getLayout('form', $vars);

        return $html;
    }

    /**
     * send request to zarinpal for payment and verify
     * @param $type
     * @param $context
     * @return bool | soap result
     */
    private function zarinpalRequest($type, $context, $host = 'zarinpal.com')
    {
        try {
            $prefix = (bool)$this->params->get('test_mode') ? 'sandbox' : 'www';

            $client = new SoapClient("https://{$prefix}.{$host}/pg/services/WebGate/wsdl", ['encoding' => 'UTF-8']);

            $type = 'Payment' . ucfirst($type);
            return $client->$type($context);

        } catch (SoapFault $e) {
            if($host == 'zarin.link')
            {
                return false;
            }
            return $this->zarinpalRequest($type, $context, 'zarin.link');
        }
    }

    /**
     * translate zarinpal text keys
     * @param $key
     * @return string
     */
    private function zarinpalTranslate($key)
    {
        // key format: ex. payment_error for payment error
        $key = 'PLG_K2STORE_PAYMENTS_ZARINPALZG_' . strtoupper($key);
        return JText::_($key);
    }

    /**
     * fix zarinpal amount
     * @param $amount
     * @return int
     */
    private function zarinpalAmount($amount)
    {
        if ((bool)$this->params->get('currency')) { // rial == 1
            $amount /= 10;
        }
        return (int)$amount;
    }
}