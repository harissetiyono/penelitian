<template>
  <div class="card">
    <div class="card-header">
      <router-link to="/" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i></router-link>
    </div>
    <div class="card-body">
      <form v-on:submit="submitPostUpdate()">
        <fieldset class="form-group">
          <label>Name</label>
          <input type="text" v-model='sentiments.id' class="form-control" placeholder="Name" ref="name" hidden>
          <input type="text" v-model='sentiments.name' class="form-control" placeholder="Name" ref="name">
        </fieldset>
        <fieldset class="form-group">
          <label>Description</label>
          <textarea v-model='sentiments.content' class="form-control" rows="3" placeholder="Testimoni"></textarea>
        </fieldset>
        <fieldset class="form-group">
          <label>Sumber</label>
          <input type="text" name="source" v-model='sentiments.source' class="form-control" placeholder="Title" ref="source">
        </fieldset>
        <button class="btn btn-primary">Update</button>
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
        id:'',
        name:'',
        content:'',
        source: '',
      },
      categories:{},
      errors: []
    }
  },

  mounted() {
    this.$refs.name.focus();
  },

  created() {
    let id = this.$route.params.id;

    axios.all([
        axios.get('/sentiment/' + id + '/edit'),
      ])
    .then(axios.spread((sentiments_res) => {
        this.sentiments = sentiments_res.data
      }))

  },

 methods:{
  submitPostUpdate() {
    let id = this.$route.params.id;

    axios.put('/sentiment/' + id, this.sentiments)
    .then(response => {
      this.sentiments = response.data

      this.$swal.fire({
        position: 'top',
        type: 'success',
        name: 'Your work has been saved',
        showConfirmButton: false,
        timer: 1500
      })
    })
    .catch(e => {
      this.errors.push(e)
    })
    this.$router.push('/')
   },
  }

}
</script>
