<?php

namespace RTippin\Messenger\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use RTippin\Messenger\Actions\Messages\AddReaction;
use RTippin\Messenger\Exceptions\FeatureDisabledException;
use RTippin\Messenger\Exceptions\ReactionException;
use RTippin\Messenger\Http\Collections\MessageReactionCollection;
use RTippin\Messenger\Http\Request\MessageReactionRequest;
use RTippin\Messenger\Http\Resources\MessageReactionResource;
use RTippin\Messenger\Models\Message;
use RTippin\Messenger\Models\MessageReaction;
use RTippin\Messenger\Models\Thread;
use Throwable;

class MessageReactionController
{
    use AuthorizesRequests;

    /**
     * Display a listing of the message reactions.
     *
     * @param Thread $thread
     * @param Message $message
     * @return MessageReactionCollection
     * @throws AuthorizationException
     */
    public function index(Thread $thread, Message $message): MessageReactionCollection
    {
        $this->authorize('viewAny', [
            MessageReaction::class,
            $thread,
        ]);

        return new MessageReactionCollection(
            $message->reactions()->with('owner')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param MessageReactionRequest $request
     * @param AddReaction $addReaction
     * @param Thread $thread
     * @param Message $message
     * @return MessageReactionResource
     * @throws Throwable|ReactionException|FeatureDisabledException|AuthorizationException
     */
    public function store(MessageReactionRequest $request,
                          AddReaction $addReaction,
                          Thread $thread,
                          Message $message): MessageReactionResource
    {
        $this->authorize('create', [
            MessageReaction::class,
            $thread,
            $message,
        ]);

        return $addReaction->execute(
            $thread,
            $message,
            $request->validated()['reaction']
        )->getJsonResource();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        //TODO
    }
}
