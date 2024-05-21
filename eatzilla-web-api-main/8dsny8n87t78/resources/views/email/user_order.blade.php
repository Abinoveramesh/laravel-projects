<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>NOTLOB | Food Delivery</title>
    <link href="https://demo.gonotlob.com/assets/img/fav.png" rel="shortcut icon" type="image/x-icon">
    <meta name="viewport" content="width=device-width">
    <style type="text/css">
        body {
            width: 100% !important;
            min-width: 100%;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            margin: 0;
            padding: 0;
            font-family: "Helvetica", "Arial", sans-serif;
        }
        
        a,
        a:hover,
        a:visited,
        a:active {
            text-decoration: none !important;
            color: inherit !important;
        }
        
        a.greenlink,
        a.greenlink:hover,
        a.greenlink:active,
        a.greenlink:visited {
            color: #0d7217 !important;
            text-decoration: none !important;
        }
        
        a.whitelink,
        a.whitelink:hover,
        a.whitelink:active,
        a.whitelink:visited {
            color: #ffffff !important;
            text-decoration: none !important;
        }
        
        .fontwhite {
            color: #ffffff !important
        }
    </style>
</head>

<body>
@php
  function sum($carry, $item)
  { 
      $carry += $item['price'];
      return $carry;
  }
@endphp
    <table style="width:100%;">
        <tbody>
            <tr>
                <td class="center" valign="top">
                    <!-- BEGIN: Header -->
                    <!-- END: Header -->

                    <table border="0" cellpadding="0" cellspacing="0" class="content" style="width: 100%; text-align: center;">
                        <tbody>
                            <tr>
                                <td>
                                    <table align="center" cellpadding="0" cellspacing="0" style="background-color:#F45B4B; width: 100%; ">
                                        <tbody>
                                            <tr style="height:70px;">
                                                <td style="padding:10px 15px;position:relative !important;text-align:center;" valign="middle">
                                                    <span style="font-size:12px;">
                                                        <a href="https://carsirent.com/page/booknowpaylater?utm_source=91bc3beabcbcf4f403f13447a4bbfc21" style="text-decoration:none !important;">
                                                            <img alt="NOTLOB" src="https://demo.gonotlob.com/assets/img/logo.png" style="width: 230px; border: 0px !important;"> 
                                                        </a> 
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" style="width:600px; margin: 0 auto;">
                                        <tbody>
                                            <tr>
                                                <td tyle="height:98px;width:100%;background-repeat:no-repeat;position:relative !important;text-align:center;line-height:24px;padding:0 !important;">
                                                    <h1>Dear  @if(isset($data->Users->name)) {{$data->Users->name}} @else Guest @endif</h1>
                                                    <p>THANK YOU FOR PLACING YOUR ORDER WITH US</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" style="width:600px; margin: 0 auto;">
                                        <tbody>
                                            <tr>
                                                <td style="height:98px;width:100%;background-repeat:no-repeat;position:relative !important;text-align:center;line-height:24px;padding:0 !important;">
                                                    <h3 style="color:#F45B4B">Your Order Receipt</h3>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" style="width:600px; margin: 0 auto; text-align: left;">
                                        <tbody>
                                            <tr>
                                                <td style="text-align: left; padding: 10px;">
                                                    {{$data->order_id}}
                                                </td>
                                            </tr>
                                            @foreach($data->Requestdetail as $r)
                                            <tr>
                                                <td style="text-align: left; padding: 10px;">
                                                    @if(!empty($r->Foodlist)) {{$r->Foodlist->name}} @endif 
                                                    X {{$r->quantity}}
                                                </td>
                                                 @php
                                                    $addon_price = 0;
                                                    $addons = $r->RequestdetailAddons->toArray();
                                                    if(!empty($addons)){
                                                      $addon_price = array_reduce($addons, "sum");
                                                    }
                                                    $food_price = ($r->food_quantity_price=='0.00')?$r->Foodlist->price:$r->food_quantity_price;
                                                    $price = $r->quantity*($food_price+$addon_price);
                                                  
                                                  @endphp
                                                <td style="text-align: right;">IQD {{$price}}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" style="width:600px; margin: 0 auto;">
                                        <tbody>
                                            <tr>
                                                <td style="border-bottom:1px solid #f45b4b">
                                                    &nbsp;
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" style="width:600px; margin: 0 auto; font-size: 11px;">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <p>Your order ID: {{$data->order_id}}</p>
                                                    <p>Delivering to: {{$data->delivery_address}}</p>
                                                    <p>Time of order: {{$data->ordered_time}}</p>
                                                    <p>Paid by: @if($data->paid_type==1) Cash @else Card @endif  </p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" style="width:600px; margin: 0 auto;">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <h5>We hope you have a lovely meal</h5>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>