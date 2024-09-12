export default {
  props: ['initial', 'refreshUrl'],

  data() {
    return {
      results: this.initial,
    }
  },

  methods: {
    refresh() {
      this.$axios.get(this.refreshUrl).then(({ data }) => {
        this.results = data.results
        setTimeout(this.refresh, 1000)
      })
    },
  },

  mounted() {
    setTimeout(this.refresh, 1000)
  },

  render() {
    return this.$scopedSlots.default({
      results: this.results,
    })
  }
}
