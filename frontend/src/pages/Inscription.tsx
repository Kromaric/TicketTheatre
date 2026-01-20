import { Flex } from "@chakra-ui/react";
import { SignupCard } from "../components/SignupCard";

export default function Inscription() {
  return (
    <Flex justify="center" align="center" minH="60vh">
      <SignupCard />
    </Flex>
  );
}