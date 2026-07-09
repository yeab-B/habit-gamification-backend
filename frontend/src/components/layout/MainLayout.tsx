import React from "react";
import { Outlet } from "react-router-dom";
import Sidebar from "./Sidebar";
import Navbar from "./Navbar";

export const MainLayout: React.FC = () => {
  return (
    <div className="flex h-screen w-screen overflow-hidden bg-background" id="main-layout">
      {/* Sidebar Navigation */}
      <Sidebar />

      {/* Main Window */}
      <div className="flex flex-1 flex-col overflow-hidden">
        {/* Top Header */}
        <Navbar />

        {/* Scrollable Main Viewport */}
        <main className="flex-1 overflow-y-auto p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
};

export default MainLayout;
