{** 2014 DPD Polska Sp. z o.o.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * prestashop@dpd.com.pl so we can send you a copy immediately.
 *
 * @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
 * @copyright 2014 DPD Polska Sp. z o.o.
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of DPD Polska Sp. z o.o.
 *}
<label class="col-lg-5">{l s='Company name:' mod='dpdpoland'}</label>
<div class="col-lg-6">{$address.company|escape:'htmlall':'UTF-8'}</div>
<div class="clearfix"></div>
<label class="col-lg-5">{l s='Name and surname:' mod='dpdpoland'}</label>
<div class="col-lg-6">{$address.name|escape:'htmlall':'UTF-8'}</div>
<div class="clearfix"></div>
<label class="col-lg-5">{l s='Street and house no.:' mod='dpdpoland'}</label>
<div class="col-lg-6">{$address.street|escape:'htmlall':'UTF-8'}</div>
<div class="clearfix"></div>
<label class="col-lg-5">{l s='Postal code:' mod='dpdpoland'}</label>
<div class="col-lg-6">{$address.postcode|escape:'htmlall':'UTF-8'}</div>
<div class="clearfix"></div>
<label class="col-lg-5">{l s='City:' mod='dpdpoland'}</label>
<div class="col-lg-6">{$address.city|escape:'htmlall':'UTF-8'}</div>
<div class="clearfix"></div>
<label class="col-lg-5">{l s='Country:' mod='dpdpoland'}</label>
<div class="col-lg-6">{$address.country|escape:'htmlall':'UTF-8'}</div>
<div class="clearfix"></div>
<label class="col-lg-5">{l s='E-mail:' mod='dpdpoland'}</label>
<div class="col-lg-6">{$address.email|escape:'htmlall':'UTF-8'}</div>
<div class="clearfix"></div>
<label class="col-lg-5">{l s='Tel. No.' mod='dpdpoland'}</label>
<div class="col-lg-6">{$address.phone|escape:'htmlall':'UTF-8'}</div>
<div class="clearfix"></div>