<?php

namespace App\Http\Controllers;

use App\Blog;
use Illuminate\Http\Request;
/**
 * 2019-04-02
 * (エラーの直接的な原因)
 * Laravelでは、{{ csrf_field() }}というものを使ってCSRF対策を行います。https://readouble.com/laravel/5.8/ja/csrf.html
 * この記述は、<form>~</form>の中に記述をすることが必要です。今回は、＠csrfではなく、{{ csrf_field() }}で記述をしています。
 * 以前まではセキュリティ対策としてセッションに保存したものとPOSTで送られてきたものをチェックしていましたよね。
 * 
 * また、併せて以下の修正を行っています。
 * 
 * regitメソッドの修正（改修）
 * 上の変更に伴うindexメソッドの変更（フロント側も対応）
 * 
 * この状態でエラーはなくなりますが、DBの登録の際に
 * 「"SQLSTATE[HY000]: General error: 1364 Field 'category_id' doesn't have a default value (SQL: insert into `newblogs` (`title`, `message`, `updated_at`, `created_at`) values (ｓ, SNS, 2019-04-02 00:39:36, 2019-04-02 00:39:36)) ◀"」
 * というエラーが発生します。(user_idもですか？？)
 * こちらのエラー対応をして、再度Gitへ　commit, push お願いします。
 * 
 * 
 * 対応方法としては、
 * 　category_id　を　null可能にする、　あるいはデフォルト値を設定するの２つがあるかと思います。
 * 　https://readouble.com/laravel/5.8/ja/migrations.html
 * 　こちらのページの「　カラム修飾子　」の部分を参考にしてもらうといいかと思います。
 * 
 * なお、migrationファイルを記述しなおして、再度migrationを行おうとするときには既にテーブルが存在する旨のエラーがでます。そのときには以下のコマンドでDBをリフレッシュしてマイグレーションをかけてくれます。
 * php artisan migrate:refresh
 *
 * 
 * 
 * 【フロント側について】
 * 1.　Laravelでは条件分岐を@if～@endifとして記載します。繰り返しは@for ～　@endfor（@foreach ～　@endforeach）です。
 * 2. コントローラーからの変数にアクセスするには、配列のキーを指定します。今回はPostsNewblog.phpで['msg' => $msg]として返したので、msgでアクセスします。
 * 
 * 
 * 
 * 以上になります。 
 */

class PostsNewblog extends Controller
{
    /**
     * 初期表示時メソッド
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request){
        /**
         *  検収内容5-1. regitメソッドでセッションを設定したので、そのセッションデーターがあれば変数に代入してフロントへ返す設定を追加しました。
         *      具体的には、セッションデーターがあればそれを変数に代入しますが、無ければNULL（空値）を設定します。
         *      Laravelではデーターを渡すために、return view()の中で配列を作成([キー名=>値])として返却します。フロント側ではこのキー名で中身を取得することができます。
         * 
         */
        //　以下、検収内容5-1
        if ( $request->session()->get('msg') ) {
    
            $msg = $request->session()->get('msg');

        } else {
            $msg = null;
        }

        return view('index',['msg' => $msg]); //　キー名をmsg として返します。併せて　resources/views/index.blade.php　も確認してください。
    }

    /**
     * ブログ登録メソッド
     *
     * @param Request $request
     * @return void
     */
    public function resist(Request $request){
        /**
         * 検収内容１．storeメソッドの中身をそのままコピペしました。
         * 検収内容２．ヴァリデーションをかけました。【参考】https://readouble.com/laravel/5.6/ja/validation.html
         * 　任意： 文字列：　長さ255バイト
         * 　必須：文字列
         * 検収内容3.　リダイレクトをさせました。以前までのheader('Location:')と同じ処理です。
         * 検収内容4.　セッションにデータを保持させました。 https://readouble.com/laravel/5.6/ja/session.html
         * 検収内容5.　セッションを使用したため、フロント画面（index対応画面）もセッションがあれば表示という処理を記載しました。
         */
        $validatedData = $request->validate([
            'title' => 'string|max:255',
            'message' => 'required|string',
        ]); // 検収内容2
        // 検収内容1
        $blog = new Blog;
        
        $blog->title = $request->title;
        
        $blog->message = $request->message;
        
        $blog->save();

        $request->session()->put('msg', '記事の登録がされました。'); // 検収内容4  // 検収内容5

        return redirect('index'); // 検収内容3
    }

    


    public function store(Request $request){
        
        $blog = new Blog;
        
        $blog->title = $request->title;
        
        $blog->message = $request->message;
        
        $blog->save();
    }
}
