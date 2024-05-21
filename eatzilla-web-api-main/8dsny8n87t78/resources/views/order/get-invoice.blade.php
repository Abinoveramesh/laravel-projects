<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>
</head>

<body>
    <table cellspacing="0" cellpadding="0" border="0" width="1170" style="margin:0 auto;border-collapse: collapse; font-family: Arial, sans-serif, 'Open Sans'">
        <tr>
            <td>
                <table cellspacing="0" cellpadding="0" border="0" style="width:1470px">
                    <tr>
                        <td style="background-color: #32176c; padding:30px 0px; vertical-align: top;" align="center">
                            <img src="{{ public_path('favicon-new.png') }}"
                                style="width:200px;">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table cellspacing="0" cellpadding="0" border="0" style="width:1470px">
                    <tr>
                        <td style=" vertical-align: top;" align="center">
                        <h1 style="font-size: 64px; text-align:center; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; color: #111827;">Tax Invoice</h1>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
      <tr>
        <td>
            <table cellspacing="0" cellpadding="0" border="0" style="width:1470px">
                <tr>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <td style="width:700px; vertical-align: top;">
                        <h3 style="margin:0;padding:0 0 10px; color: #111827;">Invoice To:</h3><span style="color: #6b7280;vertical-align: text-top;">{{$userDetails->name}}</span>
                        <br> <br>
                        <h3 style="margin:0;padding:0 0 10px; color: #111827;">Customer Address:</h3><span style="color: #6b7280;vertical-align: text-top;">{{$requestDetails->delivery_address}}</span>
                        <br> <br>
                        <h3 style="margin:0;padding:0 0 10px; color: #111827;">Order ID:</h3><span style="color: #6b7280;vertical-align: text-top;">{{$requestDetails->order_id}}</span>
                    </td>
                    <td  style="width:700px; vertical-align: top;">
                        <h3 style="margin:0;padding:0 0 10px; color: #111827;">Restaurant Name:</h3><span style="color: #6b7280;vertical-align: text-top;">{{$restaurantDetail->restaurant_name}}</span>
                        <br> <br>
                        <h3 style="margin:0;padding:0 0 10px; color: #111827;">Restaurant Address:</h3><span style="color: #6b7280;vertical-align: text-top;">{{$restaurantDetail->address}}</span>
                        <br> <br>
                    </td>
                </tr>
               
            </table>
        </td>
      </tr>
        <!-- <tr>
            <td height="70">
                <table align="left" cellspacing="0" cellpadding="0">
                    <tr>
                        <th align="left" width="150"><h3 style="margin:0;padding:0 0 10px; color: #111827;">Invoice To</h3></th>
                        <th align="left" width="150"><h3 style="margin:0;padding:0 0 10px; color: #111827;">GSTIN</h3></th>
                        <th align="left" width="150"><h3 style="margin:0;padding:0 0 10px; color: #111827;">Customer Address</h3></th>
                        <th align="left" width="150"><h3 style="margin:0;padding:0 0 10px;">Terms</h3></th>
                    </tr>
                    <tr>
                        <td style="color: #6b7280;vertical-align: text-top;">Krishna Praksh K </td>

                        <td style="color: #6b7280;vertical-align: text-top;">Unregistered</td>

                        <td style="vertical-align: text-top; color: #6b7280">The Chennail Silks Opp.<br> Lakshmi Complex, <br>
                Coimbatore, <br> Tamil Nadu, <br>India. (Ram Nagar)
                        </td>
                        <td style="vertical-align: text-top">0 Days  </td>
                    </tr>
                </table>
                {{-- <table align="left" cellspacing="0" cellpadding="0" style="width:1111827px">
                    <tr style="background-color: #fff;">
                        <th align="left" style="background-color: #fff;" width="320px"> <h3>Invoice Detail</h3>
                        </th>
                        <th align="left" style="background-color: #fff;" width="320px"> <h3>Billed To</h3>
                        </th>
                        <th align="left" style="background-color: #fff;" width="320px"> <h3>Invoice Number</h3></th>
                        <th align="left" style="background-color: #fff;" width="320px"> <h3>Terms</h3></th>
                    </tr>
                    <tr style="">
                        <td style="padding: 10px 10px;">Unwrapped</td>

                        <td style="padding: 10px 10px;">The Boring Company</td>

                        <td style="padding: 10px 10px;">San Javier</td>
                        <td style="padding: 10px 10px;">CA 1234</td>
                    </tr>
                    <tr style="">
                        <td style="padding: 10px 10px;">Fake Street 123</td>

                        <td style="padding: 10px 10px;">Frisco</td>

                        <td style="padding: 10px 10px;">$0.00</td>
                        <td style="padding: 10px 10px;">$0.00</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 10px;">San Javier</td>
                        <td style="padding: 10px 10px;"> CA 1118270</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 10px;">CA 1234</td>
                    </tr>
                </table> --}}
            </td>

        </tr> -->
        <tr>
            <td height="30">
            </td>
        </tr>
        <tr>
            <td>
                <table cellspacing="0" cellpadding="0" style="width:1470px;border:1px solid #ccc;">
                    <tr style="padding: 10px 10px;">
                    <th align="left" style="padding: 30px; color: #111827;border:1px solid #ccc;" width="100px">Sr No
                        </th>
                        <th align="left" style="padding: 30px; color: #111827; text-align:center;border:1px solid #ccc;" width="300px">Description
                        </th>
                        <th align="left" style="padding: 30px; color: #111827; text-align:center;border:1px solid #ccc;" width="200px">Quantity
                        </th>
                        <th align="left" style="padding: 30px; color: #111827; text-align:center;border:1px solid #ccc;" width="200px">Unit Price</th>
                        <th align="left" style="padding: 30px; color: #111827; text-align:center;border:1px solid #ccc;" width="200px">Amount</th>
                    </tr>
                    @foreach($foodDetails as $key => $foodDetail)
                        <tr style="color: #111827;padding: 30px; text-align:center;">
                            <td style="color: #111827;padding: 30px; text-align:center;border:1px solid #ccc;">{{$key+1}}</td>

                            <td style="color: #111827;padding: 30px; text-align:center;border:1px solid #ccc;">{{$foodDetail->Foodlist->name}}</td>

                            <td style="color: #111827;padding: 30px; text-align:center;border:1px solid #ccc;">{{$foodDetail->quantity}}</td>
                            <td style="color: #111827;padding: 30px; text-align:center;border:1px solid #ccc; font-family: DejaVu Sans;">{{DEFAULT_CURRENCY_SYMBOL .$foodDetail->Foodlist->price}}</td>
                            <td style="color: #111827;padding: 30px; text-align:center;border:1px solid #ccc; font-family: DejaVu Sans;">{{DEFAULT_CURRENCY_SYMBOL .$foodDetail->Foodlist->price * $foodDetail->quantity}}</td>
                        </tr>
                    @endforeach
                    <tr style="color: #111827;padding: 30px">
                        <td style="color: #111827;padding: 30px; text-align:center;border:1px solid #ccc;"></td>

                        <td style="color: #111827;padding: 30px; text-align:center;border:1px solid #ccc; font-weight: 600;  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;">Sub Total</td>

                        <td style="color: #111827;padding: 30px; text-align:center;border:1px solid #ccc;"></td>
                        <td style="color: #111827;padding: 30px; text-align:center;border:1px solid #ccc;"></td>
                        <td style="color: #111827;padding: 30px; text-align:center;border:1px solid #ccc;  font-family: DejaVu Sans;">{{DEFAULT_CURRENCY_SYMBOL .$requestDetails->item_total}}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td height="30">
            </td>
        </tr>
        <tr>
            <td height="">
                <table cellspacing="0" cellpadding="0"style="width:1470px;">
                    <tr>
                        <td ></td>
                        <td style="width: 550px;"><h4 style="padding: 10px; margin:0; vertical-align: top;">Restaurant Packing Charges</h4></td>
                        <td style="width: 100px;"><h3 style="padding: 10px; margin:0; vertical-align: top; font-family: DejaVu Sans;">{{DEFAULT_CURRENCY_SYMBOL .$requestDetails->restaurant_packaging_charge}}</h3></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="width: 350px;"><h4 style="padding: 10px; margin:0; vertical-align: top;">Restaurant Taxes </h4></td>
                        <td style="width: 100px;"><h3 style="padding: 10px; margin:0; vertical-align: top; font-family: DejaVu Sans;">{{DEFAULT_CURRENCY_SYMBOL .$requestDetails->tax}}</h3></td>
                    </tr>
                    
                    <tr>
                        <td></td>
                        <td style="width: 350px;"><h4 style="padding: 10px; margin:0; vertical-align: top;">Invoice Total</h4></td>
                        <td style="width: 100px;"><h3 style="padding: 10px; margin:0; vertical-align: top; font-family: DejaVu Sans;">{{DEFAULT_CURRENCY_SYMBOL .($requestDetails->tax + $requestDetails->restaurant_packaging_charge + $requestDetails->item_total)}}</h3></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td height="">
                <table cellspacing="0" cellpadding="0" style="width:1470px; border-top:1px solid #ccc; border-bottom:1px solid #ccc; margin-top: 20px; padding-top: 20px; ">
                    <tr>
                        <td style="width: 500px; vertical-align: top;"><h5 style="padding-top: 20px; padding-bottom: 20px; margin:0; vertical-align: top;">Invoice Total in words</h5></td>
                        <td style="width: 600px; vertical-align: top;"><h5 style="padding-top: 20px; padding-bottom: 20px; margin:0; vertical-align: top; text-align:right;">{{$invoiceAmount}} Only</h5></td>
                    </tr>
                  
                </table>
            </td>
        </tr>
        <tr>
            <td height="">
                <table cellspacing="0" cellpadding="0" style="width:1470px; border-top:1px solid #ccc; margin-top: 180px; padding-top: 20px; ">
                    <tr>
                        <td style="vertical-align: top;"><h4 style="padding-top: 30px; margin:0; vertical-align: top;">Disclaimer: Attached is the invoice for the restaurant services provided by the outlet. For items not covered in the attached invoice, the outlet shall be responsible to issue an invoice directly to you.</h4></td>
                    </tr>
                  
                </table>
            </td>
        </tr>

    </table>
</body>

</html>