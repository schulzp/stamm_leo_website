var LEO = {
  searchform: {
    VALUE: "Suchbegriff",
    REGEXP: new RegExp('^\s*$'),
    clear: function(input) {
      if (input.value == this.VALUE)
        input.value = '';
      else
        input.select();
    },
    reset: function(input) {
      if (this.REGEXP.test(input.value))
        input.value = this.VALUE;
    }
  }
}
