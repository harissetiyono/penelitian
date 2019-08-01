<template>
  <div class="card">
    <div class="card-header">
      <router-link to="/" class="btn btn-sm btn-secondary"> Back</router-link>
    </div>
    <div class="card-body">
      <form v-on:submit="submitPost()">
        <fieldset class="form-group">
          <label>Penerbit</label>
          <input type="text" name="name" v-model='sentiments.name' class="form-control" placeholder="Penerbit" ref="name">
        </fieldset>
        <fieldset class="form-group">
          <label>Testimoni</label>
          <textarea name="content" v-model='sentiments.content' class="form-control" rows="3" placeholder="Testimoni"></textarea>
        </fieldset>
        <fieldset class="form-group">
          <label>Sumber</label>
          <input type="text" name="source" v-model='sentiments.source' class="form-control" placeholder="sumber" ref="source">
        </fieldset>
        <button class="btn btn-primary">Create</button>
      </form>
    </div>
  </div>
</template>

<script>
  import axios from 'axios';

  export default {
  data:function() {
    return {
      sentiments: {
        name:'',
        content:'',
        source:'',
      },
      errors: []
    }
  },

  // Fetches sentiments when the component is created.
  // created() {
  //   axios.get('/categories')
  //   .then(response => { this.categories = response.data })
  //   .catch(e => {
  //     this.errors.push(e)
  //   })
  // },

  mounted() {
    this.$refs.name.focus();
  },

  methods:{
    submitPost() {
      axios.post('/sentiment', this.sentiments)
      .then(
        response => {
          this.sentiments = response.data

          this.$swal({
            type: 'success',
            name: 'Your work has been saved',
            showConfirmButton: false,
            timer: 1500
          })
        },
      )
      .catch(e => { this.errors.push(e) })
      this.$router.push('/')
     }
   }
}
</script>
