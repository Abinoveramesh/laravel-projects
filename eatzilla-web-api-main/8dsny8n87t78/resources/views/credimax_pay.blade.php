<script src="https://credimax.gateway.mastercard.com/checkout/version/56/checkout.js" data-error="errorCallback" data-cancel="cancelCallback" data-complete="completeCallback">
    </script>
    <script>
        function errorCallback(error) {
            console.log(JSON.stringify(error));
        }

        function completeCallback(data) {
            console.log(JSON.stringify(data));
        }

        function cancelCallback() {
            console.log('Payment cancelled');

        }
        Checkout.configure({
      session: {
        id: '{{$sessionid}}'
      },
      merchant: 'E11742950',
      order: {
        amount: function () {
          return '$total';
        },
        currency: 'IQD',
        description: 'Food Delivery from Notlob',
        id: '{{$transactionid}}'
      },
      billing: {
        address: {
          street: '{{$address}}',
          city: 'Manama',
          postcodeZip: '{{$block_number}}',
          stateProvince: 'Manama',
          country: 'BHR'
        }
      },
      interaction: {
        operation: 'PURCHASE',
        merchant: {
          name: 'Notlob Food Delivery',
          address: {
            line1: '312 Falcon Tower Diplomatic Area',
            line2: 'Manama Bahrain'
          },
          email: 'support@gonotlob.com',
          phone: '+973 1700 1550',
          logo: 'https://gonotlob.com/assets/images/common/logoorange.png'
        },
        locale: 'en_US',
        theme: 'default',
        displayControl: {
          billingAddress: 'HIDE',
          customerEmail: 'HIDE',
          orderSummary: 'SHOW',
          shipping: 'HIDE'
        }
      }
    });
    Checkout.showPaymentPage();
    </script>