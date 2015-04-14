# xmr-integration
XMR Integration / Demo

This is a complete PHP integration of monero. Once setup you will have a working membership site with:
- Login/Registration
- Generation of payment id
- Deposit of funds (automatically added to account after X confirmations)
- Withdraw of funds (added to processing queue and processed automatically)
- Admin Panel displaying current balances and other useful info (e.g status of daemon and wallet)
- With some minor changes, you can add multiple cryptonote types of currencies/assets

The script comes with cron.php, which is the processing script. It can be setup to run forever in the background, or even 
as a cron job. Read the comment in cron.php for more info.

For install instructions simply open install.php

Server requirements are:
- PHP 5.3.3 +
- MySQLi
- BCMath
- PHP Short Tags

The license is specified in each file, everything coded by me is "do whatever the fuck you want license" ;)

Keep in mind that this script is ment as a proof of concept, it may work fine to develop your site from 
this script, but the intention is only to easily show in action how to integrate monero so that you can copy 
the code needed to get it implementated in your own applications.

Security wise I would generally advise to keep hotwallets on separate servers and not integrated directly like here.
But this adds a layer of complexity that far exeeds the intention of this project.

- TheKoziTwo (https://bitcointalk.org/index.php?action=profile;u=4129)
