<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order Completed Mail</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  </head>
  <body>
    <table cellpadding="10" cellspacing="0" style="max-width: 740px; margin: auto auto;font-family: 'Rubik', Arial, Helvetica, sans-serif;font-size: 16px;color: #7c7c7c;" border="0" bgcolor="#FFF">
      <tbody>
        <tr>
          <td>
            <table cellpadding="10" cellspacing="0" style="max-width: 720px;margin: auto auto;border: 2px solid #24336a;" border="0" bgcolor="#24336a">
              <tbody>
                <tr>
                  <td>
                    <table cellpadding="0" cellspacing="0" style="max-width: 700px;margin: auto auto;border:1px solid #656e7a;padding: 10px;" border="0" bgcolor="#24336a">
                      <tbody>
                        <tr>
                          <td align="center" style="padding: 25px 0px" colspan="3">
                            <!-- <img src="https://dev2api.truely.co.in/public/favicon-new.png" alt="Logo" style="width: 150px;margin-bottom: 10px;
                                margin: 0 auto; " /> -->                          
                              <img src="{{URL::asset('public/favicon-new.png')}}" alt="Logo" style="width: 150px;margin-bottom: 10px;
                                margin: 0 auto; " />
                          </td>
                        </tr>
                        <tr>
                          <td width="30"></td>
                          <td width="590" align="center">
                            <table cellpadding="30" cellspacing="1" border="0" style="border: 1px solid #e7eaec; background-image: url('bg1.png');background-repeat: no-repeat;background-position: left top;background-size: 250px;" bgcolor="#FFFFFF">
                              <tr>
                                <td align="center">
                                  <div style="height: 20px"></div>
                                  <h2 style="font-family: 'Rubik', Arial, Helvetica, sans-serif;font-size: 26px;color: #24336a;padding-bottom: 0px;margin: 0;font-weight:bold;letter-spacing: .5px; "> Greetings from TRUELY</h2>
                                  <div style="height: 10px"></div>
                                  <p style="font-family: 'Rubik', Arial, Helvetica, sans-serif;font-size: 16px;color: #212121;padding-bottom: 0px;margin: 0;line-height: 1.6;"> Your order was delivered successfully !!!</p>
                                   
                                  <div style="height: 40px"></div>



                                  <table width="100%" cellspacing="0" cellpadding="0" style="width:100%;font-family: 'Rubik', Arial, Helvetica, sans-serif;">
                                    <tbody>
                                      <tr>
                                        <td style="margin:0 auto;padding:0;display:block;max-width:800px;clear:both;">

                                          <div style="margin:0 auto;padding:0;max-width:600px;border-radius:3px;display:block;">
                                           <div>
                                              <table width="100%" cellspacing="0" cellpadding="0">
                                                <tbody>
                                                  <tr>
                                                  <td width="35%" style="margin-bottom:15px;font-weight:bold;font-size:12px;min-width:100px;max-width:100%;display:inline-block;vertical-align:top;">
                                                    <p style="margin:0px;padding:0px;line-height:1.6;height:35px;">
                                                      <span style="color:rgb(26, 26, 26);">
                                                        <b><span style="font-size:14px;margin:0px;padding:0px;line-height:1.6;">Order No:</span></b></span></p>
                                                        <h5 style="margin:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px;">{{$requestCompletedDetails->order_id}}<br>
                                                        </h5>
                                                      </td>
                                                      <td width="65%" style="margin-bottom:15px;font-weight:bold;font-size:12px;min-width:100px;max-width:100%;display:inline-block;vertical-align:top;">
                                                        <p style="margin:0px;padding:0px;line-height:1.6;height:35px;"><span style="color:rgb(26, 26, 26);">
                                                          <b>
                                                        <span style="font-size:14px;margin:0px;padding:0px;line-height:1.6;">Restaurant</span></b></span></p>
                                                        <h5 style="margin:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px;">{{$restaurantDetail->restaurant_name}}<br></h5>
                                                      </td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                              </div>
                                              <div style="margin:0 auto;padding:0;max-width:600px;border-radius:3px;display:block;">
                                                <table width="100%" cellspacing="0" cellpadding="0">
                                                  <tbody>
                                                    <tr>
                                                      <td><br></td>
                                                      <td style="margin:0 auto;padding:0;display:block;max-width:590px;clear:both;">
                                                        <br>
                                                        <div style="margin:0 auto;padding:0;max-width:590px;border-radius:3px;display:block;">
                                                          <div>
                                                            <div>
                                                              <table width="100%" cellspacing="0" cellpadding="0">
                                                                <tbody>
                                                                  <tr>
                                                                    <td>
                                                                      <p style="margin:0px 0px 5px;padding:0px;font-weight:400;line-height:1.6;height:40px">
                                                                        <span style="color:rgb(26, 26, 26);">
                                                                          <span style="font-family: 'Rubik', Arial, Helvetica, sans-serif;font-size: 18px;color: #24336a;padding-bottom: 0px;margin: 0;font-weight:bold;letter-spacing: .5px; ">Your Order Summary:</span>
                                                                        </span>
                                                                      </p>
                                                                    </td>
                                                                  </tr>
                                                                </tbody>
                                                              </table>
                                                            </div>
                                                            <div>
                                                              <table width="100%" cellspacing="0" cellpadding="0">
                                                                <tbody>
                                                                  <tr>
                                                                    <td width="100%" style="font-size:15px;min-width:100px;max-width:100%;display:inline-block;vertical-align:top;">
                                                                      <p style="margin:0px;padding:0px;line-height:1.6;">
                                                                        <span style="color:rgb(26, 26, 26);">
                                                                          <span style="font-size:15px;margin:0px;padding:0px;line-height:1.6;"><span style="width:130px;display: inline-block;">Order No:</span> <b>{{$requestCompletedDetails->order_id}}</b></span>
                                                                        </span>
                                                                        <br>
                                                                      </p>
                                                                    </td>
                                                                  </tr>
                                                                  <tr>
                                                                    <td width="100%" style="font-size:15px;min-width:100px;max-width:100%;display:inline-block;vertical-align:top;">
                                                                      <p style="margin:10px 0px 0px;padding:0px;">
                                                                        <span style="color:rgb(26, 26, 26);">
                                                                          <span style="font-size:15px;margin:0px;padding:0px;"><span style="width:130px;display: inline-block;">Order placed at:</span> <b>{{$requestCompletedDetails->ordered_time}}</b>
                                                                          </span>
                                                                        </span>
                                                                        <br>
                                                                      </p>
                                                                    </td>
                                                                  </tr>
                                                                  <tr>
                                                                    <td width="100%" style="font-size:15px;min-width:100px;max-width:100%;display:inline-block;vertical-align:top;">
                                                                      <p style="margin:10px 0px 0px;padding:0px;line-height:1.2;">
                                                                        <span style="color:rgb(26, 26, 26);">
                                                                          <span style="font-size:15px;margin:10px 0px 0px;padding:0px;line-height:1.2;"><span style="width:130px;display: inline-block;">Order delivered at:</span> <b>{{$requestCompletedDetails->delivered_time}}</b>
                                                                          </span>
                                                                        </span>
                                                                        <br>
                                                                      </p>
                                                                    </td>
                                                                  </tr>
                                                                  <tr>
                                                                    <td width="100%" style="font-size:15px;min-width:100px;max-width:100%;display:inline-block;vertical-align:top;">
                                                                      <p style="margin:10px 0px 0px;padding:0px;line-height:1.2;">
                                                                      <span style="color:rgb(26, 26, 26);">
                                                                        <span style="font-size:15px;margin:10px 0px 0px;padding:0px;line-height:1.2;"><span style="width:130px;display: inline-block;">Order Status:</span> <b>Delivered</b>
                                                                        </span>
                                                                      </span>
                                                                      <br>
                                                                    </p>
                                                                  </td>
                                                                </tr>
                                                                <tr>
                                                                  <td width="100%" style="margin-top:25px;padding:0;margin-bottom:12px;font-size:15px;min-width:100px;max-width:100%;display:inline-block;vertical-align:top;">
                                                                    <p style="margin:0px;padding:0px;line-height:1.6;height: 30px;">
                                                                      <span style="color:rgb(26, 26, 26);font-size:15px;margin:0px;padding:0px;line-height:1.6;">Ordered from:
                                                                      </span>
                                                                    </p>
                                                                    <h5 style="line-height:1.5;margin:0;margin-bottom:5px;color:#1a1a1a;font-weight:400;font-size:15px;"><b>{{$restaurantDetail->restaurant_name}}</b>
                                                                      <br></h5>
                                                                      <p style="margin:0px;padding:0px 0 10px 0;font-weight:normal;line-height:1.6;">
                                                                        <span style="color:rgb(88, 88, 88);">
                                                                          <span style="font-size:15px;margin:0px;padding:0px;font-weight:normal;line-height:1.6;">{{$restaurantDetail->address}}
                                                                          </span>
                                                                        </span>
                                                                        <br>
                                                                      </p>
                                                                    </td>
                                                                  </tr>
                                                                  <tr>
                                                                    <td>
                                                                          <p style="margin:0px;padding:0px;height: 30px;color:rgb(26, 26, 26);font-size:15px;margin:0px;padding:0px;">Delivery To:</p>
                                                                          <h5 style="line-height:1.5;margin:0;margin-bottom:5px;color:#1a1a1a;font-weight:400;font-size:15px;"><b>{{$userDetails->name}}</b></h5>
                                                                          <h5 style="margin:0px;padding:0px;line-height:1.6;margin-bottom:5px;color:#585858;font-weight:normal;font-size:15px;">{{$requestCompletedDetails->delivery_address}}<br></h5>
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </div>
                                                                <br>
                                                                <div>
                                                                  <table width="100%" cellspacing="0" cellpadding="0">
                                                                    <thead style="text-align:left;background:#e9e9e9;border-collapse:collapse;border-spacing:0;border-color:#ccc;">
                                                                      <tr>
                                                                        <th style="margin:0;padding:15px 15px;font-size:15px;color:#24336a;">Item Name<br></th>
                                                                        <th style="margin:0;padding:15px 15px;font-size:15px;text-align:right;padding-right:172px;color:#24336a;">Quantity<br></th>
                                                                        <th align="right" style="margin:0;padding:15px 15px;font-size:15px;color:#24336a;">Price<br></th>
                                                                      </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                      <tr>
                                                                        <td style="height:5px"></td>
                                                                      </tr>
                                                                      @foreach($foodDetails as $foodDetail)
                                                                      <tr>
                                                                        @php
                                                                          $foodPrice = $foodDetail->Foodlist->price;
                                                                        @endphp
                                                                        @if(!empty($foodDetail->RequestdetailAddons))
                                                                          @foreach($foodDetail->RequestdetailAddons as $addons)
                                                                            @php
                                                                              $foodPrice = $foodPrice + $addons->price;
                                                                            @endphp
                                                                          @endforeach
                                                                        @endif
                                                                        @php
                                                                          $foodPrice = $foodPrice * $foodDetail->quantity
                                                                        @endphp
                                                                        <td style="vertical-align:top;margin:0;padding:10px;font-weight:normal;font-size:15px;">{{$foodDetail->Foodlist->name}}<br></td>
                                                                        <td style="margin:0;text-align:right;padding:10px;font-weight:normal;font-size:15px;padding-right:200px;">{{$foodDetail->quantity}}<br></td>
                                                                        <td align="right" style="margin:0;padding:10px;font-weight:normal;font-size:15px;padding-right:15px;">{{DEFAULT_CURRENCY_SYMBOL .($foodPrice)}}<br></td>
                                                                      </tr>
                                                                      <tr width="100%">
                                                                        <td><div style="height:2px;width:100%;background:#e9e9e9;clear:both;"><br></div></td>
                                                                        <td><div style="height:2px;width:100%;background:#e9e9e9;clear:both;"><br></div></td>
                                                                        <td><div style="height:2px;width:100%;background:#e9e9e9;clear:both;"><br></div></td>
                                                                      </tr>
                                                                      <tr>
                                                                        <td style="height:5px"></td>
                                                                      </tr>
                                                                      @endforeach                                                                      
                                                                      <tr>
                                                                        <td style="height:15px"></td>
                                                                      </tr>
                                                                      <tr>
                                                                        <td width="80%" scope="row" colspan="2" style="margin:0;padding:10px 0;text-align:right;font-weight:normal;border:0;font-size:15px;">Item Total:<br></td>
                                                                        <td width="20%" style="margin:0;padding:5px 0;font-weight:normal;border-bottom:0px solid #e9e9e9;font-size:15px;text-align:right;border:0;padding-right:15px;">{{DEFAULT_CURRENCY_SYMBOL .$requestCompletedDetails->item_total}}<br></td>
                                                                      </tr>
                                                                      <tr>
                                                                        <td style="height:10px"></td>
                                                                      </tr>
                                                                      <tr>
                                                                        <td width="80%" scope="row" colspan="2" style="margin:0;padding:10px 0;text-align:right;font-weight:normal;border:0;font-size:15px;">Restaurant Packing Charges:<br></td>
                                                                        <td width="20%" style="margin:0;padding:5px 0;font-weight:normal;border-bottom:0px solid #e9e9e9;font-size:15px;text-align:right;border:0;padding-right:15px;">{{DEFAULT_CURRENCY_SYMBOL .$requestCompletedDetails->restaurant_packaging_charge}}<br></td>
                                                                      </tr>
                                                                      <tr>
                                                                        <td style="height:10px"></td>
                                                                      </tr>
                                                                      <tr>
                                                                        <td width="80%" scope="row" colspan="2" style="margin:0;padding:10px 0;text-align:right;font-weight:normal;border:0;font-size:15px;">Delivery Charge:<br></td>
                                                                        <td width="20%" style="margin:0;padding:5px 0;font-weight:normal;border-bottom:0px solid #e9e9e9;font-size:15px;text-align:right;border:0;padding-right:15px;">{{DEFAULT_CURRENCY_SYMBOL .$requestCompletedDetails->delivery_charge}}<br></td>
                                                                      </tr>
                                                                      <tr>
                                                                        <td style="height:10px"></td>
                                                                      </tr>
                                                                      <tr>
                                                                        <td width="80%" scope="row" colspan="2" style="margin:0;padding:10px 0;text-align:right;font-weight:normal;border:0;font-size:15px;">Discount Applied (WELCOMEBACK):<br></td>
                                                                        <td width="20%" style="margin:0;padding:5px 0;font-weight:normal;border-bottom:0px solid #e9e9e9;font-size:15px;text-align:right;border:0;padding-right:15px;">- {{DEFAULT_CURRENCY_SYMBOL .($requestCompletedDetails->offer_discount + $requestCompletedDetails->restaurant_discount)}}<br></td>
                                                                      </tr>
                                                                      <tr>
                                                                        <td style="height:10px"></td>
                                                                      </tr>
                                                                      <tr>
                                                                        <td width="80%" scope="row" colspan="2" style="margin:0;padding:10px 0;text-align:right;font-weight:normal;border:0;font-size:15px;">Taxes:<br></td>
                                                                        <td width="20%" style="margin:0;padding:5px 0;font-weight:normal;border-bottom:0px solid #e9e9e9;font-size:15px;text-align:right;border:0;padding-right:15px;">{{DEFAULT_CURRENCY_SYMBOL .$requestCompletedDetails->tax}}<br></td>
                                                                      </tr>
                                                                      <tr width="100%">
                                                                        <td><div style="width:100%;clear:both;"><br></div></td>
                                                                        <td><div style="min-height:15px;width:100%;clear:both;"><br></div></td>
                                                                        <td><div style="min-height:15px;width:100%;clear:both;"><br></div></td>
                                                                      </tr>
                                                                      <tr style="color:#79b33b;background:#f9f9f9;">
                                                                        <th width="80%" scope="row" colspan="2" style="margin:0;padding:15px 0;text-align:right;font-weight:bold;border:0;font-size:15px;">Order Total:<br></th>
                                                                        <td width="20%" style="margin:0;padding:15px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:15px;text-align:right;border:0;padding-right:15px;">{{DEFAULT_CURRENCY_SYMBOL .$requestCompletedDetails->bill_amount}}<br></td>
                                                                      </tr>
                                                                      <tr><td><br></td></tr>
                                                                    </tbody>
                                                                  </table>
                                                                </div>
                                                                <div><p style="border-top:1px solid rgb(204, 204, 204);padding:15px 0;color:rgb(88, 88, 88);font-size:15px;margin:0"><b>Disclaimer</b>:  Attached is the invoice for the restaurant services provided by the outlet. For items not covered in the attached invoice, the outlet shall be responsible to issue an invoice directly to you.<br></p></div>
                                                              </div>
                                                            </div>
                                                          </td>
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                  </div>
                                            </div>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>



                                </td>
                              </tr>
                            </table>
                          </td>
                          <td width="30"></td>
                        </tr>
                        <tr>
                          <td align="center" colspan="3">
                            <div style="height: 30px"></div>
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