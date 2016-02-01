{* Страница заказа *}

{$meta_title = "Ваш заказ №`$invoice.transaction`" scope=parent}

{if $invoice.status == 'approved'}
	<H1>Заказ успешно оплачен.</H1>
	<p>Сумма: {$invoice.amount}</p>
	<br>
	<p>Ваш заказ №:{$invoice.transaction}</p>
	<br>
{else}
	<H1>Ошибка оплаты.</H1>
	<p>Код ошибки: {$invoice.error_code}</p>
	<br>
<p>Описание ошибки :{$invoice.error_message}</p>
{/if}


