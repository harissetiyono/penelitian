@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <router-view></router-view>
        </div>
    </div>
</div>

<modal name="hello-world">
  <div class="card">
    <div class="card-header">
      Import CSV
    </div>
    <div class="card-body">
    <form action="{{ url('importExcel') }}" method="POST" enctype="multipart/form-data">
      {{ csrf_field() }}
      <blockquote class="blockquote mb-0">
        <p>Silahkan melakukan upload file berupa format .csv, pastikan format data sesuai dengan contoh.</p>
        <fieldset class="form-group">
          <input type="file" name="import_file" class="form-control">
        </fieldset>
        <fieldset class="form-group">
          <input type="submit" class="btn btn-primary" value="Upload"></button>
        </fieldset>
        <footer class="blockquote-footer">contoh format : <a href="{{ url('contoh.csv') }}">contoh.csv</a></footer>
      </blockquote>
      </form>
    </div>
  </div>
</modal>

@endsection
