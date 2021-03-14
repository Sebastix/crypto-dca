# Automated Crypto DCA tool for multiple exchanges

_Please be aware this is beta software. Test thoroughly with small amounts of money at first. This software is provided "as is" and comes without warranty. See [LICENSE](LICENSE)._

## This is a fork of Bitcoin DCA tool
Forked from https://github.com/Jorijn/bitcoin-dca  
I made this fork to support multiple assets from the Kraken exchange.

## Requirements
* You need to have an account on a supported exchange;
* You need to have Docker installed: https://docs.docker.com/get-docker/;
* You need to have an API key active on a supported exchange. It needs **read**, **trade** and **withdraw** permission.

## Supported Exchanges
| Exchange | URL | Currencies |
|------|------|------|
| Kraken | https://kraken.com/ | EUR |

## About this software
The DCA tool is built with flexibility in mind, allowing you to specify your own schedule of buying and withdrawing. A few examples that are possible:

* Buy each week, never withdraw.
* Buy monthly and withdraw at the same time to reduce exchange risk.
* Buy each week but withdraw only at the end of the month to save on withdrawal fees.

## How to use this tool
@TODO add instructions

## Development
See [docker/development/README.md](docker/development/README.md)

## Support
You can visit the Bitcoin DCA Support channel on Telegram: https://t.me/bitcoindca

## Contributing
Contributions are highly welcome! Feel free to submit issues and pull requests on https://github.com/jorijn/bitcoin-dca.

Like the work of Jorijn? Buy him a 🍺 by sending some sats to `bc1quqjfmnldh9nfnxpucyvxh9pc63jyp0qdkpmf32`.
