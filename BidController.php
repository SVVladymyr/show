<?php

namespace App\Http\Controllers;

use Storage;
use App\Http\Requests\SearchBidRequest;
use App\Http\Requests\StoreBidRequest;
use App\Http\Requests\UpdateBidRequest;
use App\Http\Resources\Bid as BidCollection;
use App\Http\Resources\BidResource;
use App\Services\BidSearchService;
use App\Models\Bid;
use App\Models\BidImage;

/**
 * @tags Объявления
 * 
 */
class BidController extends Controller
{
    /**
     * Show all bids
     *
     * @param App\Http\Requests\SearchBidRequest $request
     * @return \Illuminate\Http\Response
     */
    public function index(SearchBidRequest $request)
    {
        $search = BidSearchService::search($request->all());

        return new BidCollection($search->paginate());
    }

    /**
     * Create bid
     *
     * @param  \App\Http\Requests\StoreBidRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBidRequest $request)
    {
        $bid = Bid::create($request->all());

        if(count($request->images) > 0)
        {
            foreach($request->images as $file)
            { 
                $uploadedFile = $file;
                $filename = time() . $uploadedFile->getClientOriginalExtension();

                Storage::disk('local')->putFileAs(
                    'bid',
                    $uploadedFile,
                    $filename
                );

                $bidImage = new BidImage();
                $bidImage->image = $filename;
                $bidImage->bid_id = $bid->id;
                $bidImage->save();

            }
        }

        return new BidResource(Bid::with('images')->find($bid->id));
    }

    /**
     * Show a particular bid
     *
     * @param  \App\Models\Bid  $bid
     * @return \Illuminate\Http\Response
     */
    public function show(Bid $bid)
    {
        return new BidResource($bid);
    }

    /**
     * Update bid
     *
     * @param  \App\Http\Requests\UpdateBidRequest  $request
     * @param  \App\Models\Bid  $bid
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBidRequest $request, Bid $bid)
    {
        $bid->update($request->all());

        if(count($request->images) > 0)
        {
            foreach($bid->images as $file)
            {
                if(is_file(storage_path('app/bid/' . $file->image)))
                {
                    unlink(storage_path('app/bid/' . $file->image));
                }

                $file->delete();
            }
            
            foreach($request->images as $file)
            { 
                $uploadedFile = $file;
                $filename = time() . $uploadedFile->getClientOriginalExtension();

                Storage::disk('local')->putFileAs(
                    'bid',
                    $uploadedFile,
                    $filename
                );

                $bidImage = new BidImage();
                $bidImage->image = $filename;
                $bidImage->bid_id = $bid->id;
                $bidImage->save();
            }
        }
        
        return new BidResource(Bid::with('images')->find($bid->id));
    }

    /**
     * Destroy bid
     *
     * @param  \App\Models\Bid  $bid
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bid $bid)
    {
        $bid->delete();

        return response(null, 204);
    }
}
