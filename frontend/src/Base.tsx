import { Flex, Box } from "@chakra-ui/react";
import { Outlet } from "react-router-dom";
import Header from "./components/layout/header";
import Footer from "./components/layout/footer";

export default function Base() {
  return (
    <Flex direction="column" minH="100vh" w="full" bg="gray.contrast">
      <Header />

      <Box as="main" flex="1" w="full" p={4}>
        <Outlet />
      </Box>

      <Footer />
    </Flex>
  );
}
