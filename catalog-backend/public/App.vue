Vue.config.performance = true


var app = new Vue({
  el: "#cadabra",
  data: {
    products: [],
    resource_url: 'http://localhost:8080/products',
    loading: false,
    currentlyEdited: null
  },
  created: function() {
    this.load();
  },
  methods: {
    load: function() {
      this.loading = true;
      this.$http.get(this.resource_url).then(function(response) {
        var json = response.data,
          products = json.data

          this.products = this.products.concat(products);
          this.resource_url = json.next_page_url;
          this.loading = false;
      }, function(error) {
        console.log(error)
        this.loading = false;
      })
    },
    onScroll: function(event) {
      var container = event.target,
        list = container.firstElementChild;

      var scrollTop = container.scrollTop,
        containerHeight = container.offsetHeight,
        listHeight = list.offsetHeight;

      var heightDiff = listHeight - containerHeight;
      var reachedBottom = heightDiff <= scrollTop

      if (!this.loading && reachedBottom) {
          this.load()
      }
    },
    editName: function(product) {
      this.beforeEditCache = product.name;
      this.currentlyEdited = product;
    },
    doneEdit: function(product) {
      if (!this.currentlyEdited) {
        return
      }

      product.title = product.title.trim();
      if (!product.title) {
        this.removeProduct();
      }
    }
  }
})
