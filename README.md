# catalog
Homework task to build a product catalog service using PHP, memcached and mysql

# requirements

Build a simple system that allows to list and edit products. Product is described as {id, name, description, price, picture url}. 

* there should be pages to list, create, edit and delete product
* products can be arranged by price or id
* support for up to 1M products
* 1k requests per seconds for product listing
* \>500 ms for listing page to open

Stack: 
* PHP (no OOP)
* mysql database
* memcached

# plan

- [ ] Build a memory only catalog on top of Slim framework (use it as baseline) 
- [ ] Make it deployeable to Heroku (free tier)
- [ ] *Optional* use Travis as CI service
- [ ] Measure baseline performance
- [ ] Add MySQL support
- [ ] Add a caching layer (memcached)
- [ ] Add auth for mutable operations
- [ ] *Optional* use proxy for SSL termination and loadbalancing
