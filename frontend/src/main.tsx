import { Provider } from "./components/ui/provider";
import { Toaster } from "./components/ui/toaster";
import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import App from "./App.tsx";
import { AuthProvider } from "./contexts/AuthContext";

createRoot(document.getElementById("root")!).render(
  <StrictMode>
    <Provider>
      <AuthProvider>
        <App />
        <Toaster />
      </AuthProvider>
    </Provider>
  </StrictMode>,
);
