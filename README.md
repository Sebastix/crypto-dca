# Work in progress - Automated DCA tool for buying any crypto asset on Kraken

_Please be aware this is work in progress software. Test thoroughly with small amounts of money at first. This software is provided "as is" and comes without warranty. See [LICENSE](LICENSE)._

## This is a fork of Bitcoin DCA tool made by Jorijn
Forked from https://github.com/Jorijn/bitcoin-dca  
I made this fork to support buying any asset which is provided on the Kraken exchange.

## Requirements
* You need to have an account on a supported exchange;
* You need to have Docker installed: https://docs.docker.com/get-docker/;
* You need to have an API key. It needs **read**, **trade** and **withdraw** permission.

## Supported Exchanges
| Exchange | URL | Currencies |
|------|------|------|
| Kraken | https://kraken.com/ | EUR |

## Supported assets for withdrawing
| Assets name | Token
|------|------|
|Bitcoin|BTC or XBT|
|Ethereum|ETH|
|Cardano|ADA|
Feel free to submit a request for other assets by adding a feature request issue: https://github.com/Sebastix/crypto-dca/issues/new/choose

## About this software
The DCA tool is built with flexibility in mind, allowing you to specify your own schedule of buying and withdrawing. A few examples that are possible:

* Buy each week, never withdraw.
* Buy monthly and withdraw at the same time to reduce exchange risk.
* Buy each week but withdraw only at the end of the month to save on withdrawal fees.

## How to use this tool
Please read the documentation from Bitcoin-DCA: [https://bitcoin-dca.readthedocs.io/en/latest/](https://bitcoin-dca.readthedocs.io/en/latest/) for all details you need to get started with this software.
In this fork, the commands you use for buying and withdrawing are slightly different.

#### Build the Docker image
```
cd ~
git clone https://github.com/Sebastix/crypto-dca.git
cd crypto-dca
docker build . -t sebastix/crypto-dca:latest
```

Buy an asset (buy for 100 euro of asset X):   
`docker run --rm -it --env-file=/home/bob/crypto-dca/.env sebastix/crypto-dca:latest buy 100 <asset>`

Withdraw all your chosen assets from your account to your wallet address:  
`docker run --rm -it --env-file=/home/bob/crypto-dca/.env sebastix/crypto-dca:latest withdraw <asset> --all`

## Development
See [docker/development/README.md](docker/development/README.md)

## Support
You can visit the Bitcoin DCA Support channel on Telegram: https://t.me/bitcoindca

## Contributing
Contributions are highly welcome! Feel free to submit issues and pull requests on https://github.com/jorijn/bitcoin-dca or on this fork https://github.com/Sebastix/crypto-dca

Like the work of Jorijn? Buy him a 🍺 by sending some sats.  
Onchain: `bc1quqjfmnldh9nfnxpucyvxh9pc63jyp0qdkpmf32`  
Lightning: `03e85b676b0e8c84088525a1377b075dc4e12197bf2974529a3a5fdbfb47e957a2`
