<?php
/**
 * This is the list of all of the events, eloquent model fired that we can hook into

retrieved
creating
created
updating
updated
saving
saved
deleting
deleted
restoring
restored
 * 
 * The retrieved event will fire when an existing model is retrieved from the database. 
 * When a new model is saved for the first time, the creating and created events will fire. 
 * If a model already existed in the database and the save method is called, the updating / updated events will fire. 
 * However, in both cases, the saving / saved events will fire.
 */
namespace App\Observers;

use App\Comment;
use App\Task;
use Log;

class CommentObserver
{
    
    
    /**
     * Handle the comment "updating" event.
     *
     * @param  \App\Comment  $comment
     * @return void
     */
   public function updating(Comment $message)
    { 
          
    }
    
    /**
     * Handle the comment "created" event.
     *
     * @param  \App\Comment  $comment
     * @return void
     */
    public function created(Comment $comment)
    { 
        $user = \Auth::user();
        /*quando si crea un commento su un task, viene inserito nello storico del task*/
        if ($comment->commentable_type == 'App\Task') {
            $task = Task::find($comment->commentable_id); 
            $task->history()->create(
                                    ['event_date'=> date("Y-m-d H:i:s", time()),
                                     'event_body'=>
                                        json_encode([
                                            'user_id'=>$user->id,
                                            'user_name'=>$user->name.' '.$user->surname,
                                            'original_task_status'=>$task->task_status,
                                            'task_status'=>$task->task_status,
                                            'comment_id'=>$comment->id,
                                            'comment_body'=>$comment->body,])
                                    ]); 
            
          
        } 
        
        /*definisco l'autore del commento*/
        
        if ($user) {
            $comment->update(['author_id'=>$user->id]);
        }
          
    }

    /**
     * Handle the comment "updated" event.
     *
     * @param  \App\Comment  $comment
     * @return void
     */
    public function updated(Comment $comment)
    {  
    }

    /**
     * Handle the comment "deleted" event.
     *
     * @param  \App\Comment  $comment
     * @return void
     */
    public function deleted(Comment $comment)
    {
        //
    }

    /**
     * Handle the comment "restored" event.
     *
     * @param  \App\Comment  $comment
     * @return void
     */
    public function restored(Comment $comment)
    {
        //
    }

    /**
     * Handle the comment "force deleted" event.
     *
     * @param  \App\Comment  $comment
     * @return void
     */
    public function forceDeleted(Comment $comment)
    {
        //
    }
    
    
}
