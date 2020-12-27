### Requirements
1. [Docker](https://docs.docker.com/)  
2. [Docker compose](https://docs.docker.com/compose/)

### Environment file

Copy and rename .env.dist from the app directory to this directory
```bash
cp .env.dist .env
```
Edit the file with your API keys and withdraw information. 
[More information here](https://bitcoin-dca.readthedocs.io/en/latest/configuration.html#available-configuration). 

### Manage your docker environment

Make sure you are in directory `docker/development`  
Run the following commands  
```bash
docker-compose up -d
```  

Now a Docker container named 'dca_php' is running in the background. You can execute now any command in this container. 
For example `docker-compose -it dca php --version` for retrieving the installed PHP version from the Docker image.

To stop the Docker container from running, execute 
```bash
docker-compose stop
```

To stop and remove the Docker container, execute
```bash
docker-compose down
```

If you wish to rebuild the Docker image from the Dockerfile, run `docker-compose --build`

### Start development

Make sure you are in directory `docker/development` 
To execute a command in the application, you can use

```bash
docker-compose exec dca php bin/bitcoin-dca <your_command>
```
Replace `<your_command>` with `balance` for example the get all your balances from the connected exchange.
All available commands you can find in the file `config/services.yaml` under `services` or read the [documentations](config/services.yaml).

Make any change in the application source files located in the `src` directory and run the command to see the results.

In some cases you would like to clear the cache:
```bash
rm -rf var/cache
```

Run tests
```bash
docker-compose exec dca composer run-script test
```