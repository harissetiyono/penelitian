<template>
  <div class="card">
    <div class="card-header">
      <router-link to="create" class="btn btn-primary float-right">Add new Data</router-link>
      <button v-on:click="update_all()" class="btn btn-success">Update Sentiment</button>
    </div>
    <div class="card-body">
      <div class="form-group">
        <div class="row">
          <div class="col-sm-6">
            <input type="text" class="form-control mb-3" v-model='search' placeholder="Search By Title" @keyup="getResults()">
          </div>
        </div>
      </div>
      <table class="table">
        <thead>
          <tr>
            <th scope="col">No</th>
            <th scope="col">Penerbit</th>
            <th scope="col">Testimoni</th>
            <th scope="col">Sumber</th>
            <th scope="col">Sentiment</th>
            <th scope="col" style="width:15%">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="value, index in sentiments.data">
            <td>{{ index+1 }}</td>
            <td>{{ value.name }}</td>
            <td>{{ value.content }}</td>
            <td>{{ value.source }}</td>
            <td>
              <p v-if="value.sentiment == '1'"><span class="badge badge-success">Positif</span></p>
              <p v-else-if="value.sentiment == '-1'"><span class="badge badge-danger">Negatif</span></p>
              <p v-else="value.sentiment == '0'"><span class="badge badge-secondary">Netral</span></p>
            </td>
            <td>
              <!-- <router-link :to="{ name : 'readSentiment', params:{id:value.id}}" class="btn-primary btn-sm"><i class="fa fa-eye"></i></router-link> -->
              <router-link :to="{ name : 'editSentiment', params:{id:value.id}}" class="btn-success btn-sm"><i class="fa fa-pencil"></i></router-link>
              <button type="button" v-on:click='deleteSentiment(value.id)' class="btn-danger btn-sm"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
        </tbody>
        </table>
        <pagination :data="sentiments" :align="'center'" :size="'small'" :limit="3" v-on:pagination-change-page="getResults"></pagination>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Pagination from 'laravel-vue-pagination';
import VSwitch from 'v-switch-case'

export default {
  data() {
    return {
      sentiments: {},
      errors: [],
      search: '',
    }
  },

  components: {
    Pagination
  },

  created() {
    axios.all([
        axios.get('/sentiment'),
      ])
    .then(axios.spread((sentiment_res) => {
        this.sentiments = sentiment_res.data
      }))
  },

  methods:{
    getResults(page) {
        if (typeof page === 'undefined') {
            page = 1;
        }

        let term = this.search

        if (term.length >= 3) {
            axios.get(`/sentiment/?page=${page}&term=${term}`)
                .then(response => this.sentiments = response.data)
                .catch(error => console.log(error))
        }else{
          axios.get(`/sentiment/?page=${page}`)
              .then(response => this.sentiments = response.data)
              .catch(error => console.log(error))
        }
     },

     refreshSentiment(post) {
        this.sentiments = sentiment.data;
      },

     deleteSentiment(id) {
          this.$swal({
              title: 'Are you sure?',
              text: "You won't be able to revert this!",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, delete it!'
          })
          .then((result) => {
              if (result.value) {
                axios.delete(`/sentiment/${id}`, { id })
                .then(response => { this.refreshSentiment(response) });

                this.$swal('Deleted!','Your file has been deleted.','success')
              }

          })
      },

      update_all(){
        axios.get('/update_all')
        .then(response => {
          this.posts = response.data

          this.$swal.fire({
            position: 'top',
            type: 'success',
            title: 'Data Sentiment berhasil diupdate',
            showConfirmButton: false,
            timer: 1500
          })
        })
        .catch(e => {
          this.errors.push(e)
        })
      }

  }
}
</script>
