Vue.config.performance = true

Vue.component('dialog-modal', {
  template: '#dialog-template',
  props: ['product']
})

var app = new Vue({
  el: "#cadabra",
  data: {
    products: [],
    resource_url: '/products?limit=10',
    loading: false,
    listingMethods: [
      {name: "id", text: "sort by id"},
      {name: "price", text: "sort by price"},
    ],
    selectedOrdering: "id",
    showModal: false,
    currentlyEditedProduct: null
  },
  created: function() {
    this.load();
  },
  methods: {
    emptyProduct: function() {
      return {name: "", description: "", image_url: "", "price": 0};
    },
    onScroll: function(event) {
      var container = event.target,
        list = container.firstElementChild;

      var scrollTop = container.scrollTop,
        containerHeight = container.offsetHeight,
        listHeight = list.offsetHeight;

      var heightDiff = listHeight - containerHeight;
      var reachedBottom = heightDiff <= scrollTop;

      if (!this.loading && reachedBottom) {
          this.load()
        }
    },
    load: function() {
      this.loading = true;
      this.$http.get(this.resource_url).then(function(response) {
        var json = response.data,
          products = json.data

          this.products = this.products.concat(products);
          this.resource_url = json.next_page;
          this.loading = false;
      }, function(error) {
        console.log(error)
        this.loading = false;
      })
    },
    selectOrdering: function(method) {
      this.selectedOrdering = method.name;
      this.products = [];
      this.resource_url = '/products?limit=10&order_by=' + method.name;
      this.load();
    }
  }
})
