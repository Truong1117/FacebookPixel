##Sparsh Facebook Pixel Extension
This extension allows to add Facebook Pixel Code in your store to track your visitors’ events and the effectiveness of your Facebook ads.

##Support: 
version - 2.3.x, 2.4.x

##How to install Extension

1.	Get the extension’s version number
Note: Refer to the below link to get the version number of the downloaded extension.
https://devdocs.magento.com/extensions/install/#get-the-extensions-composer-name-and-version
2.	Navigate to your Magento project directory and update your composer.json file
Command: composer require sparsh/magento-2-facebook-pixel-extension:<version>
  
#How to set up and install Facebook Pixel:
  
  https://www.facebook.com/business/help/952192354843755?id=1205376682832142

#Enable Extension:
- php bin/magento module:enable Sparsh_FacebookPixel
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable Sparsh_FacebookPixel
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

