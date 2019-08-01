<template>
  <div class="card">
    <div class="card-header">
      <router-link to="/" class="btn btn-sm btn-secondary"> Back</router-link>
    </div>
    <div class="card-body">
      <h2>Title : {{ posts.title }}</h2>
      <p>Description : {{ posts.description }}</p>
    </div>
  </div>
</template>

<script>

export default {
  data() {
    return {
      posts: [],
      errors: []
    }
  },
  created() {
    let id = this.$route.params.id;

    axios.get('/posts/' + id)
    .then(response => {
      this.posts = response.data
    })
    .catch(e => {
      this.errors.push(e)
    })

  }
}
</script>
