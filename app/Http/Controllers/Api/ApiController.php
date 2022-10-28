<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    //

    private $commentEndpoint = 'https://jsonplaceholder.typicode.com/comments';
    private $allPostEndpoint = 'https://jsonplaceholder.typicode.com/posts';


    public function topPosts()
    {
        $allPosts = $this->getAllPosts();
        $topPosts = $this->getTopPosts($allPosts);
        return response()->json($topPosts);
    }

    public function comments (Request $request)
    {
        $commentsCollection = collect(json_decode(file_get_contents($this->commentEndpoint)));

        $filteredComments = $commentsCollection->when($request->has('postId'), function ($query) use ($request) {
            return $query->where('postId', $request->postId);
        })
        ->when($request->has('name'), function ($query) use ($request) {
            return $query->where('name', $request->name);
        })
        ->when($request->has('email'), function ($query) use ($request) {
            return $query->where('email', $request->email);
        })
        ->when($request->has('body'), function ($query) use ($request) {
            return $query->where('body', $request->body);
        });

        return response()->json($filteredComments);

    }

    private function getAllPosts()
    {
        $allPosts = collect(json_decode(file_get_contents($this->allPostEndpoint)));
        return $allPosts;
    }

    private function getTopPosts($allPosts)
    {
        $topPosts = [];
        foreach ($allPosts as $post) {
            $topPosts[] = [
                'post_id' => $post->id,
                'post_title' => $post->title,
                'post_body' => $post->body,
                'total_number_of_comments' => count($this->getComments($post->id))
            ];
        }

        $postCollect = collect($topPosts);
        $sortedPosts = $postCollect->sortByDesc('total_number_of_comments');

        return $sortedPosts;
    }

    private function getComments($postId)
    {
        $comments = collect(json_decode(file_get_contents($this->commentEndpoint)));
        $postComments = $comments->where('postId', $postId);
        return $postComments;
    }

    
}
