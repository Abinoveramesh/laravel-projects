<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500&display=swap" rel="stylesheet">
	<style>
		@page { margin: 1cm; }
		body{margin:0;}
		html{margin:0;}
	</style>
</head>
<html>
<body style="margin:0">
	@php
	function sum($carry, $item)
	{
	$carry += $item['price'];
	return $carry;
	}
	@endphp
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-bottom: 1px dotted #000; font-family: Roboto,Helvetica Neue,sans-serif;">
	<tr>
			<td align="center">ORDER REQUEST</td>
		</tr>
		<tr>
			<td align="center">{{APP_NAME}}</td>
		</tr>
		<tr>
			<td align="center">
				<span style="font-size: 12px;">ORDER NUMBER</span><br>
				<b>{{$data->order_id}}</b>
			</td>
		</tr>
		<tr>
			<td align="center">&nbsp;</td>
		</tr>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-bottom: 1px dotted #000; font-size:10px; text-align:center; font-family: Roboto, "Helvetica Neue", sans-serif;">
		<tr>
			<td align="center">&nbsp;</td>
		</tr>
		<tr>
			<td>CUSTOMER NAME: @if(!empty($data->Users)) {{$data->Users->name}} @else Guest User @endif</td>
		</tr>
		<tr>
			<td>@if(!empty($delivery_address->flat_no) && $delivery_address->flat_no!='NULL')
				<b>Flat no</b> : {{$delivery_address->flat_no}}&nbsp;
				@endif
				@if(!empty($delivery_address->landmark) && $delivery_address->landmark!='NULL')
				<b>Landmark</b> : {{$delivery_address->landmark}}&nbsp;
				@endif
				@if(!empty($delivery_address->address_direction) && $delivery_address->address_direction!='NULL')
				<b>Address direction</b> : {{$delivery_address->address_direction}}&nbsp;
				@endif
				@if(!empty($delivery_address->block_number) && $delivery_address->block_number!='NULL')
				<b>Block number</b> : {{$delivery_address->block_number}}&nbsp;
				@endif
				@if(!empty($delivery_address->building) && $delivery_address->building!='NULL')
				<b>Building</b> : {{$delivery_address->building}}&nbsp;
				@endif
				@if(!empty($delivery_address->address) && $delivery_address->address!='NULL')
				<b>Address</b> : {{$delivery_address->address}}&nbsp;
				@endif
				<br>
				Mobile Number: @if(!empty($data->Users)) {{$data->Users->phone}} @else Not Provided @endif
			</td>
		</tr>
		<tr>
			<td align="center">&nbsp;</td>
		</tr>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-bottom: 1px dotted #000; line-height:12px; text-align:center;font-family: Roboto, "Helvetica Neue", sans-serif; font-size:14px; font-weight:bold">
		<tr>
			<td align="center">&nbsp;</td>
		</tr>
		<tr>
			<td>@if(!empty($data->Restaurants)) {{$data->Restaurants->restaurant_name}}@endif</td>
		</tr>
		<tr>
			<td align="center">&nbsp;</td>
		</tr>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-bottom: 1px dotted #000; font-size:10px; text-align:left;font-family: Roboto, "Helvetica Neue", sans-serif;">
		<tr>
			<td align="center">&nbsp;</td>
		</tr>
		@php $i=1; @endphp
		@foreach($data->Requestdetail as $value)
		<tr valign="top">
			<td>{{$i}} </td>
			<td>@if(!empty($value->Foodlist)) {{$value->Foodlist->name}} @else - @endif X {{$value->quantity}} <br>
				@if(count($value->RequestdetailAddons)>0)
				(
				@foreach($value->RequestdetailAddons as $v)
				{{ $loop->first ? '' : ', ' }} {{$v->name}}
				@endforeach
				)
				@endif
			</td>
			<td style="text-align: left;">

				@php
				$addon_price = 0;
				$addons = $value->RequestdetailAddons->toArray();
				if(!empty($addons)){
				$addon_price = array_reduce($addons, "sum");
				}
				$food_price = ($value->food_quantity_price=='0.00')?$value->Foodlist->price:$value->food_quantity_price;
				$price = ($value->quantity * ($food_price + $addon_price));

				@endphp

				@if(!empty($value->Foodlist)) <span style="font-family: DejaVu Sans;">{{DEFAULT_CURRENCY_SYMBOL}}</span> {{$price}} @else - @endif
			</td>
		</tr>
		@php $i=$i+1; @endphp
		@endforeach
		<tr>
			<td colspan="3" align="center">&nbsp;</td>
		</tr>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-bottom: 1px dotted #000; font-size:10px; text-align:left;font-family: Roboto, "Helvetica Neue", sans-serif;">
	<tr>
		<td align="center">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td> </td>
		<td>Subtotal</td>
		<td style="text-align: left;"><span style="font-family: DejaVu Sans;">{{DEFAULT_CURRENCY_SYMBOL}}</span> {{ $data->item_total }}</td>
	</tr>
	<tr valign="top">
		<td> </td>
		<td>Delivery Fee</td>
		<td style="text-align: left;"><span style="font-family: DejaVu Sans;">{{DEFAULT_CURRENCY_SYMBOL}}</span> {{ $data->delivery_charge }}</td>
	</tr>
	<tr valign="top">
		<td> </td>
		<td>Discount</td>
		<td style="text-align: left;"><span style="font-family: DejaVu Sans;">{{DEFAULT_CURRENCY_SYMBOL}}</span> {{ $data->restaurant_discount }}</td>
	</tr>
	<tr>
		<td colspan="3" align="center">&nbsp;</td>
	</tr>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-bottom: 1px dotted #000; font-size:14px; line-height:14px; text-align:center; font-family: Roboto, "Helvetica Neue", sans-serif;">
		<tr>
			<td align="center">&nbsp;</td>
		</tr>
		<tr>
			<td>TOTAL: <b style="font-family: Roboto, "Helvetica Neue", sans-serif;"><span style="font-family: DejaVu Sans;">{{DEFAULT_CURRENCY_SYMBOL}}</span> {{ $data->bill_amount }}</b></td>
		</tr>
		<tr>
			<td align="center">&nbsp;</td>
		</tr>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-bottom: 1px dotted #000; font-size:16px;line-height:16px; text-align:center; font-family: Roboto, "Helvetica Neue", sans-serif;">
		<tr>
			<td align="center">&nbsp;</td>
		</tr>
		<tr>
			<td><b style="font-family: Roboto, "Helvetica Neue", sans-serif;">
				@php

				switch ((int) $data->paid_type) {
				case 1:
				echo 'CASH';
				break;
				case 2:
				echo 'PREPAID';
				break;
				case 3:
				echo 'PREPAID';
				break;
				default:
				echo '';
				break;
				}
				@endphp
				</b>
			</td>
		</tr>
		<tr>
			<td align="center">&nbsp;</td>
		</tr>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-bottom: 1px dotted #000; font-size:10px; text-align:center; font-family: Roboto, "Helvetica Neue", sans-serif;">
		<tr>
			<td align="center">&nbsp;</td>
		</tr>
		<tr>
			<td>{{date('D, M d, Y',strtotime($data->ordered_time))}}<br>
				Ordered At {{date('h:i a',strtotime($data->ordered_time))}}
			</td>
		</tr>
		<tr>
			<td align="center">&nbsp;</td>
		</tr>
	</table>
</body>

</html>