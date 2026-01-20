import { Flex } from "@chakra-ui/react";
import { LoginCard } from "../components/LoginCard";

export default function Connexion() {
  return (
    <Flex justify="center" align="center" minH="60vh">
      <LoginCard />
    </Flex>
  );
}