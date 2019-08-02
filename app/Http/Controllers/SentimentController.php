<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\Dataset\ArrayDataset;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;
use Phpml\ModelManager;
use DB;
use Storage;
use Importer;
use App\Sentiment;
use Illuminate\Support\Facades\Input;

ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit', '-1');

class SentimentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function homepage()
    {
      return view('sentiment');
    }

    public function index(Request $request)
    {
      $term = $request->input('term');
      return $this->_allSentiments($term);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return Sentiment::create($request->all());

        $input = $request->all();

        $preProcessing = $this->preProcessing($input['content']);
        $sentiment_predict = $this->sentiment_predict($preProcessing);

        DB::table('sentiments')->insert(
            [
              'name' => $input['name'],
              'content' => $input['content'],
              'source' => $input['source'],
              'sentiment' => $sentiment_predict[0],
          ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Sentiment::findOrfail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return Sentiment::findOrfail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $sentiment = Sentiment::findOrfail($id);
      // $sentiment->update($request->all());

      $input = $request->all();

      $preProcessing = $this->preProcessing($input['content']);
      $sentiment_predict = $this->sentiment_predict($preProcessing);

      DB::table('sentiments')
          ->where('id', $input['id'])
          ->update([
            'name' => $input['name'],
            'content' => $input['content'],
            'source' => $input['source'],
            'sentiment' => $sentiment_predict[0],
        ]);

      return $sentiment;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $sentiments = Sentiment::findOrfail($id);
      if ($sentiments->delete()) {
          return $this->_allSentiments();
      } else {
          return response()->json(425, ['delete' => 'Error deleting record']);
      }
    }

    public function _allSentiments($term = null)
    {
      if ($term != null) {
            $sentiment = DB::table('sentiments')
            ->where('name', 'like', '%'.$term.'%')
            ->paginate(10);
            return response()->json($sentiment, 200);
      }else{
          $sentiment = Sentiment::paginate(10);
          return response()->json($sentiment, 200);
      }
    }

    public function sentiment_training()
    {
      $training = DB::table('training')->pluck('content');
      $training = json_decode(json_encode($training), true);

      $labels = DB::table('training')->pluck('label');
      $labels = json_decode(json_encode($labels), true);

      Storage::put('training.json', json_encode($training));
      Storage::put('labels.json', json_encode($labels));

      $vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
      $vectorizer->fit($training);
      $vectorizer->transform($training);

      $transformer = new TfIdfTransformer($training);
      $transformer->fit($training);
      $transformer->transform($training);;

      $classifier = new SVC(Kernel::RBF, $cost = 1000);
      $classifier->train($training, $labels);

      $filepath = storage_path('app/public/data_training.svm');
      $modelManager = new ModelManager();
      $modelManager->saveToFile($classifier, $filepath);

      $status = 'true';
      echo "berhasil";
    }

    public function sentiment_predict($content = null)
    {
      $training = json_decode(Storage::get('training.json'));
      $labels = json_decode(Storage::get('labels.json'));

      $test[] = $content;

      // TF transform testing
      $vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
      $vectorizer->fit($training);
      $vectorizer->transform($training);
      $vectorizer->transform($test);

      //TFIDF transform testing
      $transformer = new TfIdfTransformer($training);
      $transformer->fit($training);
      $transformer->transform($training);
      $transformer->transform($test);

      //restore svm model
      $filepath = storage_path('app/public/data_training.svm');
      $modelManager = new ModelManager();
      $restoredClassifier = $modelManager->restoreFromFile($filepath);
      $predictedLabels = $restoredClassifier->predict($test);

      return $predictedLabels;
    }

    public function test()
    {
      $teks = "Alhamdulillah paket sudah saya terima dengan baik, packing rapi, kemaren pesan hari ini sudah sampai, penjual ramah, responnnya pun cepat sekaliii, terima kasiiih., tapi ad 2 halaman yangbtulisannya sangat kabur bahkan gak bisa terbaca.";
      $preProcessing = $this->preProcessing($teks);
      $sentiment_predict = $this->sentiment_predict($preProcessing);

      dd($sentiment_predict);
    }

    public function update_all_sentiment()
    {
      $sentiment = Sentiment::all()->toArray();

      foreach ($sentiment as $key => $value) {
        $preProcessing = $this->preProcessing($value['content']);
        $sentiment_predict = $this->sentiment_predict($preProcessing);

        DB::table('sentiments')
            ->where('id', $value['id'])
            ->update(['sentiment' => $sentiment_predict[0]]);
      }

      return true;
    }

    public function preProcessing($text)
    {
      $baku = DB::table('baku')->select('tidak_baku', 'baku')->pluck('baku','tidak_baku')->toArray();

      $lowercase = mb_strtolower($text);

      $remove_character = preg_replace('/[^A-Za-z\s]/',' ', $lowercase);
      $replace_word     = $remove_character;

      foreach ($baku as $key => $value) {
        $replace_word = preg_replace('/\b'.$key.'\b/', $value, $replace_word);
      }

      $stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
      $stemmer  = $stemmerFactory->createStemmer();
      $steming   = $stemmer->stem($replace_word);

      $get_stopword = DB::table('stopword')->get()->toArray();

      $stopwords = array_values(array_column($get_stopword, 'kata'));
      $remove_words = array_diff(explode(" ",$steming), $stopwords);
      $c_remove_words = implode(" ", $remove_words);

      $paragraph = implode(" ", $remove_words);

      $paragraph = trim(preg_replace('/\s+/', " ", $paragraph));

      // dd($paragraph);
      return $paragraph;
    }

    function csvToArray($filename = '', $delimiter = ';')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
            {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        // dd($data);
        return $data;
    }


    public function importExcel(Request $request)
    {
        $request->validate([
            'import_file' => 'required'
        ]);

        if( Input::file('import_file') ) {
            $path = Input::file('import_file')->getRealPath();
        } else {
            return back()->withErrors('');
        }

        $customerArr = $this->csvToArray($path);

        for ($i = 0; $i < count($customerArr); $i ++)
        {
            $preProcessing = $this->preProcessing($customerArr[$i]['content']);
            $sentiment_predict = $this->sentiment_predict($preProcessing);

            $data[] = [
              'name' => $customerArr[$i]['name'],
              'content' => $customerArr[$i]['content'],
              'source' => $customerArr[$i]['source'],
              'date' => $customerArr[$i]['date'],
              'sentiment' => $sentiment_predict[0],
            ];
        }

        DB::table('sentiments')->insert($data);

        return redirect('/');
    }
}
