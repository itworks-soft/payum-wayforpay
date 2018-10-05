<?php
namespace Payum\WayForPay\Action;

use App\Entity\Payment\Payment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;

class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Payment $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details['merchantDomainName'] = 'www.apk-inform.com';//$_SERVER['HTTP_HOST'];
        $details['merchantTransactionSecureType'] = 'AUTO';
        $details['language'] = 'ru';
		$details['returnUrl'] = $request->getToken()->getAfterUrl();
		$details['orderReference'] = $payment->getOrder()->getId() . '-' . $payment->getId();
		$details['orderDate'] = $payment->getOrder()->getCreatedAt()->getTimestamp();
        $details['amount'] = $payment->getTotalAmount();
        $details['currency'] = $payment->getOrder()->getRealCurrency();
		$details['productName'] = [$payment->getDescription()];
		$details['productPrice'] = $details['amount'];
		$details['productCount'] = [1];
		$details['clientFirstName'] = $payment->getOrder()->getUser()->getFirstName();
		$details['clientLastName'] = $payment->getOrder()->getUser()->getLastName();
		$details['clientEmail'] = $payment->getClientEmail();
		$details['clientPhone'] = $payment->getOrder()->getUser()->getPhone();
		$details['clientAccountId'] = $payment->getClientEmail();

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() == 'array'
        ;
    }
}
