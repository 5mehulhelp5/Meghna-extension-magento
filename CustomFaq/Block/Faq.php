<?php
namespace Codilar\CustomFaq\Block;

use Magento\Framework\View\Element\Template;

class Faq extends Template
{
    public function getFaqData()
    {
        return [
            [
                'category' => 'General',
                'question' => 'How do I create an account?',
                'answer' => 'Click on the "Create an Account" link at the top right corner and fill in your details.'
            ],
            [
                'category' => 'General',
                'question' => 'How can I reset my password?',
                'answer' => 'Go to the login page, click "Forgot Your Password?", and enter your email to receive a reset link.'
            ],
            [
                'category' => 'Shipping',
                'question' => 'What are your delivery times?',
                'answer' => 'Standard delivery usually takes 3-5 business days depending on your location.'
            ],
            [
                'category' => 'Shipping',
                'question' => 'Do you ship internationally?',
                'answer' => 'Yes, we ship to most countries worldwide. Shipping rates are calculated at checkout.'
            ],
            [
                'category' => 'Returns',
                'question' => 'What is your return policy?',
                'answer' => 'You can return any unused item within 30 days of delivery for a full refund.'
            ]
        ];
    }

    public function getCategories()
    {
        $faqs = $this->getFaqData();
        $categories = array_unique(array_column($faqs, 'category'));
        return array_values($categories);
    }
}
