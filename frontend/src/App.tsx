import React, { useEffect } from "react";
import { RouterProvider } from "react-router-dom";
import AppProvider from "@/app/providers/AppProvider";
import router from "@/app/router";
import { useApplicationStore } from "@/app/store";

export const App: React.FC = () => {
  const { theme } = useApplicationStore();

  // Apply dark mode styling class to root document tag
  useEffect(() => {
    const root = window.document.documentElement;
    if (theme === "dark") {
      root.classList.add("dark");
    } else {
      root.classList.remove("dark");
    }
  }, [theme]);

  return (
    <AppProvider>
      <RouterProvider router={router} />
    </AppProvider>
  );
};

export default App;
