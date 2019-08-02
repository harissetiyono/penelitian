<template>
  <div class="card">
    <div class="card-header">
      <router-link to="/" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</router-link>
    </div>
    <div class="card-body">
      <form v-on:submit="submitPostUpdate()">
        <fieldset class="form-group">
          <label>Penerbit</label>
          <select name="name" class="form-control" v-model="sentiments.name" required>
            <option disabled value="">Pilih Penerbit</option>
            <option value="Intan Pariwara">Intan Pariwara</option>
            <option value="Erlangga">Erlangga</option>
            <option value="Tiga Serangkai">Tiga Serangkai</option>
            <option value="Yudhistira">Yudhistira</option>
          </select>
        </fieldset>
        <fieldset class="form-group">
          <label>Testimoni</label>
          <textarea name="content" v-model='sentiments.content' class="form-control" rows="3" placeholder="contoh: buku bagus, kualitas oke" required></textarea>
        </fieldset>
        <fieldset class="form-group">
          <label>Sumber</label>
          <input type="text" name="source" v-model='sentiments.source' class="form-control" placeholder="contoh: bukalapak.com, tokopedia.com" ref="source" required>
        </fieldset>
        <fieldset class="form-group">
          <label>Tanggal</label>
          <div class="col-md-4 pl-0">
            <datepicker input-class="form-control" v-model='sentiments.date' format="yyyy-MM-dd"></datepicker>
          </div>
        </fieldset>
        <button class="btn btn-primary">Simpan</button>
      </form>
    </div>
  </div>
</template>

<script>
  import axios from 'axios';
  import Datepicker from 'vuejs-datepicker';

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

  components: {
    Datepicker
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
