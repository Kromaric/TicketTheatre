import { Flex } from "@chakra-ui/react";
import Header from "./components/layout/header";
import Footer from "./components/layout/footer";

export default function Base() {
  return (
    <Flex direction="column" minH="100vh" w="full" bg="gray.contrast">
      <Header />
      <Flex as="main" flex="1" direction="column" w="full" p={4}>
        {/* Le contenu principal de l'application sera inséré ici */}
      </Flex>
      <Footer />
    </Flex>
  );
}
