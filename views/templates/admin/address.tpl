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
<label>{l s='Company name:' mod='dpdpoland'}</label>
<div class="margin-form">{$address.company|escape:'htmlall':'UTF-8'}</div>

<label>{l s='Name and surname:' mod='dpdpoland'}</label>
<div class="margin-form">{$address.name|escape:'htmlall':'UTF-8'}</div>

<label>{l s='Street and house no.:' mod='dpdpoland'}</label>
<div class="margin-form">{$address.street|escape:'htmlall':'UTF-8'}</div>

<label>{l s='Postal code:' mod='dpdpoland'}</label>
<div class="margin-form">{$address.postcode|escape:'htmlall':'UTF-8'}</div>

<label>{l s='City:' mod='dpdpoland'}</label>
<div class="margin-form">{$address.city|escape:'htmlall':'UTF-8'}</div>

<label>{l s='Country:' mod='dpdpoland'}</label>
<div class="margin-form">{$address.country|escape:'htmlall':'UTF-8'}</div>

<label>{l s='E-mail:' mod='dpdpoland'}</label>
<div class="margin-form">{$address.email|escape:'htmlall':'UTF-8'}</div>

<label>{l s='Tel. No.' mod='dpdpoland'}</label>
<div class="margin-form">{$address.phone|escape:'htmlall':'UTF-8'}</div>