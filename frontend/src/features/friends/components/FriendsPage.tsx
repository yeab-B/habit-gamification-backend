import React from "react";

export const FriendsPage: React.FC = () => {
  return (
    <div className="space-y-6" id="friends-page">
      <div className="flex flex-col gap-2">
        <h1 className="text-3xl font-bold tracking-tight text-foreground">Friends</h1>
        <p className="text-muted-foreground">Interact with your friends, check their leaderboard standings, and invite others.</p>
      </div>
      <div className="rounded-xl border border-border bg-card p-6 shadow-sm">
        <div className="h-60 rounded-lg border border-dashed border-border flex items-center justify-center text-sm text-muted-foreground">
          Leaderboard and Friends List Placeholder
        </div>
      </div>
    </div>
  );
};

export default FriendsPage;
