<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function destroy($id)
    {
        $notif = Notification::find($id);

        if (!$notif) {
            return response()->json(['message' => 'Notification introuvable'], 404);
        }

        $notif->delete();

        return response()->json(['message' => 'Notification supprimée avec succès']);
    }
}
