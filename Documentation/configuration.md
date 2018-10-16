# Configuration

## :house: Access

To access to Paga+Tarde admin panel, we need to open the prestashop admin panel and follow the next steps:

1 – Modules and Services => Modules and Services
![Step 1](./prestashop_installation_1.png?raw=true "Step 1")

2 – Search => Configure
![Step 2](./prestashop_configuration_2.png?raw=true "Step 2")

3 – Paga+Tarde
![Step 3](./prestashop_configuration_3.png?raw=true "Step 3")

## :clipboard: Options
In Paga+tarde admin panel, we can set the following options:

| Field &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;| Description<br/><br/>
| :------------- |:-------------| 
| Public Key(*) |  String you can get from your [Paga+Tarde profile](https://bo.pagamastarde.com/shop).
| Secret Key(*) |  String you can get from your [Paga+Tarde profile](https://bo.pagamastarde.com/shop). 
| How to open payment  |  - Redirect => After checkout, the user will be redirected to Paga+Tarde side to fill the form. Recommended option. <br/> - Iframe => After checkout, the user will watch a pop-up with Paga+Tarde side to fill the form without leave the current page
| Title(*)     |  Payment title to show in checkout page. By default:"Instant financing"
| Product simulator    |  Choose if we want to use installments simulator inside product page, in positive case, you can chose the simulator type. Recommended option: MINI
| Product simulator position  |  Choose the place where you want to watch the simulator.
| Number of installments by default | Number of installments by default to use in simulator
| Maximum numbers of installments  | Maximum number of installments by default to use in simulator   
| Minimum amount | Minimum amount to use the module and show the payment method to checkout 
| Ok url | Location where user will be redirected after a succesful payment. This string will be concatenated to the base url to build the full url
| Ko url | Location where user will be redirected after a wrong payment. This string will be concatenated to the base url to build the full url 