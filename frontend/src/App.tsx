import { BrowserRouter, Routes, Route } from "react-router-dom";
import Base from "./Base";
import Home from "./pages/Home";
import Connexion from "./pages/Connexion";
import Inscription from "./pages/Inscription";
import Theatre from "./pages/Theatre";
import Programme from "./pages/Programme";
import SpectacleDetails from "./pages/SpectacleDetails";
import MesReservations from "./pages/MesReservations.tsx";
import Reservation from "./pages/Reservation.tsx";

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route element={<Base />}>
          <Route path="/" element={<Home />} />
          <Route path="/login" element={<Connexion />} />
          <Route path="/register" element={<Inscription />} />
          <Route path="/theatre" element={<Theatre />} />
          <Route path="/programme" element={<Programme />} />
          <Route path="/spectacle/:id" element={<SpectacleDetails />} />
          <Route path="/mes-reservations" element={<MesReservations/>} />
          <Route path="/reserver/:id" element={<Reservation/>}/>
        </Route>
      </Routes>
    </BrowserRouter>
  );
}
