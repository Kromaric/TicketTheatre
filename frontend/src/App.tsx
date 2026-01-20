import { BrowserRouter, Routes, Route } from "react-router-dom";
import Base from "./Base";
import Home from "./pages/Home";
import Connexion from "./pages/Connexion";
import Inscription from "./pages/Inscription";
import Theatre from "./pages/Theatre";
import Programme from "./pages/Programme";
import Reservation from "./pages/Reservation";

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route element={<Base />}>
          <Route path="/" element={<Home />} />
          <Route path="/connexion" element={<Connexion />} />
          <Route path="/inscription" element={<Inscription />} />
          <Route path="/theatre" element={<Theatre />} />
          <Route path="/programme" element={<Programme />} />
          <Route path="/reservation" element={<Reservation />} />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}
