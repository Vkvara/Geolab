<?php

namespace Geolab\WebApi\Model\Resolver;

use Geolab\WebApi\Helper\Data;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Sales\Model\Order;

class GetOrderData implements ResolverInterface
{
    /**
     * @var Order
     */
    private $order;
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;
    /**
     * @var Data
     */
    private $config;

    public function __construct(
        Order $order,
        QuoteFactory $quoteFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        Data $config
    ) {
        $this->order = $order;
        $this->quoteFactory = $quoteFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->config = $config;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if ($this->config->getGeneralConfig('enable')) {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($args['orderId'], 'masked_id');
            $quoteId = $quoteIdMask->getQuoteId();

            $quote = $this->quoteFactory->create()->load($quoteId);
            $orderNumber = $quote->getReservedOrderId();

            $items = [];

            foreach ($quote->getAllItems() as $item) {
                $items[] = [
                    'id' => $item->getId(),
                    'name' => $item->getName(),
                    'sku' => $item->getSku(),
                    'price' => $item->getPrice(),
                    'quantity' => $item->getQty()
                ];
            }

            return [
                'order_number' => $orderNumber,
                'order_date' => $quote->getCreatedAt(),
                'items' => $items
            ];
        }
    }
}
