# brandastic-newsletter-coupon
Magento 2 plugin to generate a coupon upon newsletter subscription

## Installation
`composer require uabassguy/brandastic-newsletter-coupon ~1.0.4`

## Further instructions
Refresh your caches

Navigate to MARKETING > Communications > Email Templates

Add New Template

Load Template : Choose (under Magento_Newsletter) Subscription Success (or choose existing Subscription Success email), and click Load Template

Add this line to the template anywhere:

    <a href='{{config path="web/unsecure/base_url"}}addpromo'>Click here to save 15% on ProductName<a/>

It just needs to link to the controller, the coupon code is already stashed in the customer session.

Change product name to match yours (in the code the sku being used is simple1).

### TODO:
Make the sku able to be chosen in admin